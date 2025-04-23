<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SongRequestController;

Route::middleware('api')->post('/songs', [SongRequestController::class, 'store']);

Route::delete('/songs/{id}', [SongRequestController::class, 'destroy']);

Route::post('/songs/{id}/play', [SongRequestController::class, 'play']);

Route::post('/songs/from-uris', [SongRequestController::class, 'getByUris']);

Route::get('/songs/current-queue', [SongRequestController::class, 'getCurrentQueue']);

