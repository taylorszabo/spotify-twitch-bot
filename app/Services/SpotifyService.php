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

        return $response->json()['tracks']['items'][0] ?? null;
    }

    public function addToQueue($uri)
    {
        $token = $this->getAccessToken();

        return Http::withToken($token)
            ->post('https://api.spotify.com/v1/me/player/queue?uri=' . urlencode($uri));
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
