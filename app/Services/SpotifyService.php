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

    public function playTrack(string $uri)
    {
        $token = $this->getAccessToken();

        // Get active device
        $deviceResponse = Http::withToken($token)->get('https://api.spotify.com/v1/me/player/devices');
        $devices = $deviceResponse->json()['devices'] ?? [];

        $activeDevice = collect($devices)->firstWhere('is_active', true)
            ?? collect($devices)->first();

        if (!$activeDevice || !isset($activeDevice['id'])) {
            return response()->json(['error' => 'No active Spotify device found'], 404);
        }

        $deviceId = $activeDevice['id'];


        $payload = [
            'uris' => [$uri],
        ];

        $response = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->put("https://api.spotify.com/v1/me/player/play?device_id={$deviceId}", $payload);

        \Log::info('Play single URI', [
            'device_id' => $deviceId,
            'payload' => $payload,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return $response;
    }

    public function getAccessToken()
    {
        return Cache::get('spotify_access_token');
    }

    public function storeAccessToken($accessToken, $expiresIn): void
    {
        Cache::put('spotify_access_token', $accessToken, $expiresIn);
    }
}
