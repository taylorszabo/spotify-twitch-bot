<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SongRequestController;

Route::middleware('api')->post('/songs', [SongRequestController::class, 'store']);

Route::delete('/songs/{id}', [SongRequestController::class, 'destroy']);

Route::post('/songs/{id}/play', [SongRequestController::class, 'play']);

Route::post('/songs/from-uris', [SongRequestController::class, 'getByUris']);

Route::get('/songs/current-queue', [SongRequestController::class, 'getCurrentQueue']);

Route::get('/twitch/token', function () {
    $accessToken = Cache::get('twitch_access_token');

    if (!$accessToken) {
        $refreshToken = Cache::get('twitch_refresh_token');

        if (!$refreshToken) {
            \Log::error('❌ No Twitch refresh token found in cache.');
            return response()->json(['error' => 'No refresh token found'], 400);
        }

        $response = Http::asForm()->post('https://id.twitch.tv/oauth2/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => config('services.twitch.client_id'),
            'client_secret' => config('services.twitch.client_secret'),
        ]);

        \Log::debug('🎯 Twitch token refresh response', ['body' => $response->json()]);

        if (!$response->ok() || !isset($response['access_token'])) {
            return response()->json(['error' => 'Failed to refresh access token', 'details' => $response->json()], 500);
        }

        $data = $response->json();
        $accessToken = $data['access_token'];

        Cache::put('twitch_access_token', $accessToken, $data['expires_in']);
        Cache::put('twitch_refresh_token', $data['refresh_token']);
    }

    return response()->json(['access_token' => $accessToken]);
});



