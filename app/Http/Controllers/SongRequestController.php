<?php
namespace App\Http\Controllers;

use App\Models\Song;
use App\Services\SpotifyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

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
            'artist' => $track['artist'],
            'uri' => $track['uri'],
            'album' => $track['album'],
            'album_image' => $track['album_image'],
            'release_year' => $track['release_year'],
        ]);


        $spotify->addToQueue($song->uri);

        return response()->json($song, 201);
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $song = Song::findOrFail($id);
        $song->delete();

        return response()->json(['message' => 'Song deleted']);
    }

    public function play($id, SpotifyService $spotify): \Illuminate\Http\JsonResponse
    {
        $songs = Song::all();

        $selected = $songs->firstWhere('id', $id);
        if (!$selected) {
            return response()->json(['error' => 'Song not found'], 404);
        }

        $uris = $songs
            ->where('id', '!=', $id)
            ->pluck('uri')
            ->prepend($selected->uri)
            ->unique()
            ->values()
            ->toArray();

        $response = $spotify->playUris($uris);

        return $response->successful()
            ? response()->json(['message' => 'Now playing from selected song.'])
            : response()->json(['error' => 'Unable to play track list'], $response->status());
    }

    public function getByUris(Request $request, SpotifyService $spotify)
    {
        $uris = $request->input('uris', []);

        $existing = Song::whereIn('uri', $uris)->get()->keyBy('uri');

        $results = [];

        foreach ($uris as $uri) {
            if ($existing->has($uri)) {
                $results[] = $existing[$uri];
            } else {
                $track = $spotify->getTrackDetails($uri);

                if ($track) {
                    $new = Song::create([
                        'spotify_id' => $track['id'],
                        'title' => $track['name'],
                        'artist' => $track['artist'],
                        'uri' => $track['uri'],
                        'album' => $track['album'],
                        'album_image' => $track['album_image'],
                        'release_year' => $track['release_year'],
                    ]);

                    $results[] = $new;
                }
            }
        }

        return $results;
    }

    public function getCurrentQueue(Request $request, SpotifyService $spotify)
    {
        $snapshot = Cache::get('spotify_queue_snapshot', []);
        $uris = is_array($snapshot) ? $snapshot : [];

        return $this->getByUris(new Request(['uris' => $uris]), $spotify);
    }

}
