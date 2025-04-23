<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Events\QueueUpdated;

class QueueSyncService
{
    public function sync(): void
    {
        $token = Cache::get('spotify_access_token');

        if (!$token) {
            Log::warning('Spotify token missing; cannot sync queue.');
            return;
        }

        $response = Http::withToken($token)
            ->get('https://api.spotify.com/v1/me/player/queue');

        if (!$response->ok()) {
            Log::error('Failed to fetch Spotify queue', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return;
        }

        $data = $response->json();
        $queue = collect($data['queue'] ?? [])->pluck('uri')->toArray();
        $current = isset($data['currently_playing']['uri']) && is_string($data['currently_playing']['uri'])
            ? $data['currently_playing']['uri']
            : null;


        $newState = array_map('strval', array_filter(array_merge([$current], $queue)));
        $previous = Cache::get('spotify_queue_snapshot', []);

        $normalizedPrevious = array_values($previous);
        $normalizedNew = array_values($newState);

        sort($normalizedPrevious);
        sort($normalizedNew);

        if ($normalizedPrevious !== $normalizedNew) {
            Cache::put('spotify_queue_snapshot', $newState, 600);
            try {
                broadcast(new QueueUpdated($newState));
            } catch (\Throwable $e) {
                Log::error('Broadcast failed', ['exception' => $e]);
            }
        }
    }
}
