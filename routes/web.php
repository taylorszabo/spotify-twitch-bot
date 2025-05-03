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

Route::get('/admin/songs', [SongAdminController::class, 'index'])->name('admin.songs');


