<?php
namespace App\Http\Controllers;

use App\Models\Song;
use App\Services\SpotifyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SongRequestController extends Controller
{
    /**
     * Store a new song request, verify it with Spotify, and add it to the queue.
     */
    public function store(Request $request, SpotifyService $spotify): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|max:255',
        ]);

        $track = $spotify->searchTrack($request->input('query'));

        if (!$track) {
            return response()->json(['error' => 'Track not found'], 404);
        }

        $existing = Song::where('spotify_id', $track['id'])->first();

        if ($existing) {
            return response()->json([
                'message' => 'Song already added.',
                'song' => $existing,
            ], 200);
        }

        $song = Song::create([
            'spotify_id' => $track['id'],
            'title' => $track['name'],
            'artist' => $track['artists'][0]['name'],
            'uri' => $track['uri'],
        ]);

        $spotify->addToQueue($song->uri);

        return response()->json($song, 201);
    }
}
