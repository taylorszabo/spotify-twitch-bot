<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Services\SpotifyService;

Route::get('/login/spotify', function () {
    $query = http_build_query([
        'client_id' => config('services.spotify.client_id'),
        'response_type' => 'code',
        'redirect_uri' => config('services.spotify.redirect_uri'),
        'scope' => 'user-modify-playback-state',
    ]);

    return redirect("https://accounts.spotify.com/authorize?$query");
});

Route::get('/callback/spotify', function (Request $request, SpotifyService $spotify) {
    $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
        'grant_type' => 'authorization_code',
        'code' => $request->code,
        'redirect_uri' => config('services.spotify.redirect_uri'),
        'client_id' => config('services.spotify.client_id'),
        'client_secret' => config('services.spotify.client_secret'),
    ]);

    $spotify->storeAccessToken(
        $response['access_token'],
        $response['expires_in']
    );

    return redirect('/')->with('success', 'Spotify linked!');
});


