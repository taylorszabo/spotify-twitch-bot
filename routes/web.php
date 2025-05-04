<?php

use App\Http\Controllers\Admin\SongAdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Services\SpotifyService;

Route::get('/login/spotify', function () {
    $query = http_build_query([
        'client_id' => config('services.spotify.client_id'),
        'response_type' => 'code',
        'redirect_uri' => config('services.spotify.redirect_uri'),
        'scope' => 'user-read-playback-state user-modify-playback-state',
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

    $data = $response->json();

    $spotify->storeAccessToken($data['access_token'], $data['expires_in']);

    if (isset($data['refresh_token'])) {
        Cache::put('spotify_refresh_token', $data['refresh_token']);
    }
});

Route::get('/login/twitch', function () {
    $query = http_build_query([
        'client_id' => config('services.twitch.client_id'),
        'redirect_uri' => config('services.twitch.redirect_uri'),
        'response_type' => 'code',
        'scope' => 'chat:read chat:edit',
    ]);

    return redirect("https://id.twitch.tv/oauth2/authorize?$query");
});

Route::get('/callback/twitch', function (Request $request) {
    $code = $request->code;

    $response = Http::asForm()->post('https://id.twitch.tv/oauth2/token', [
        'client_id' => config('services.twitch.client_id'),
        'client_secret' => config('services.twitch.client_secret'),
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => config('services.twitch.redirect_uri'),
    ]);

    $data = $response->json();

    Cache::put('twitch_access_token', $data['access_token'], $data['expires_in']);
    Cache::put('twitch_refresh_token', $data['refresh_token']);

    return redirect('/')->with('success', 'Twitch connected!');
});

Route::get('/admin/songs', [SongAdminController::class, 'index'])->name('admin.songs');



