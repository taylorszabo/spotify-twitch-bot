<?php

// app/Services/SpotifyService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SpotifyService
{
    public function searchTrack($query)
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)->get('https://api.spotify.com/v1/search', [
            'q' => $query,
            'type' => 'track',
            'limit' => 1,
        ]);

        $track = $response->json()['tracks']['items'][0] ?? null;

        if (!$track) {
            return null;
        }

        return [
            'id' => $track['id'],
            'name' => $track['name'],
            'artist' => $track['artists'][0]['name'],
            'uri' => $track['uri'],
            'album' => $track['album']['name'],
            'album_image' => $track['album']['images'][0]['url'] ?? null,
            'release_year' => substr($track['album']['release_date'], 0, 4),
        ];
    }


    public function addToQueue($uri)
    {
        $token = $this->getAccessToken();

        return Http::withToken($token)
            ->post('https://api.spotify.com/v1/me/player/queue?uri=' . urlencode($uri));
    }

    public function playUris(array $uris)
    {
        $token = $this->getAccessToken();

        $deviceResponse = Http::withToken($token)->get('https://api.spotify.com/v1/me/player/devices');
        $devices = $deviceResponse->json()['devices'] ?? [];

        $activeDevice = collect($devices)->firstWhere('is_active', true)
            ?? collect($devices)->first();

        if (!$activeDevice || !isset($activeDevice['id'])) {
            return response()->json(['error' => 'No active Spotify device found'], 404);
        }

        $deviceId = $activeDevice['id'];

        $payload = [
            'uris' => $uris,
        ];

        $response = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->put("https://api.spotify.com/v1/me/player/play?device_id={$deviceId}", $payload);

        \Log::info('Play multiple URIs', [
            'device_id' => $deviceId,
            'payload' => $payload,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return $response;
    }

    public function getTrackDetails(string $uri): ?array
    {
        return Cache::remember("spotify_track_{$uri}", now()->addHours(24), function () use ($uri) {
            $token = $this->getAccessToken();

            preg_match('/spotify:track:(.+)/', $uri, $matches);
            $id = $matches[1] ?? null;

            if (!$id) {
                return null;
            }

            $response = Http::withToken($token)->get("https://api.spotify.com/v1/tracks/{$id}");

            if (!$response->ok()) {
                \Log::error('Failed to fetch track from Spotify', ['uri' => $uri, 'response' => $response->body()]);
                return null;
            }

            $track = $response->json();

            return [
                'id' => $track['id'],
                'name' => $track['name'],
                'artist' => $track['artists'][0]['name'] ?? 'Unknown',
                'uri' => $track['uri'],
                'album' => $track['album']['name'] ?? 'Unknown',
                'album_image' => $track['album']['images'][0]['url'] ?? '',
                'release_year' => substr($track['album']['release_date'] ?? '0000', 0, 4),
            ];
        });
    }

    public function getAccessToken(): ?string
    {
        $token = Cache::get('spotify_access_token');

        if ($token) {
            return $token;
        }

        \Log::info('Access token missing. Attempting to refresh...');
        return $this->refreshAccessToken();
    }



    public function storeAccessToken($accessToken, $expiresIn): void
    {
        Cache::put('spotify_access_token', $accessToken, $expiresIn);
    }

    public function refreshAccessToken(): ?string
    {
        $refreshToken = Cache::get('spotify_refresh_token');

        if (!$refreshToken) {
            \Log::warning('No refresh token found');
            return null;
        }

        $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => config('services.spotify.client_id'),
            'client_secret' => config('services.spotify.client_secret'),
        ]);

        if (!$response->ok()) {
            \Log::error('Failed to refresh Spotify token', ['body' => $response->body()]);
            return null;
        }

        $data = $response->json();

        $this->storeAccessToken($data['access_token'], $data['expires_in']);

        return $data['access_token'];
    }

}
