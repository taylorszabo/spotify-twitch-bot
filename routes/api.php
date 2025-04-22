<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SongRequestController;

Route::middleware('api')->post('/songs', [SongRequestController::class, 'store']);

Route::delete('/songs/{id}', [SongRequestController::class, 'destroy']);

Route::post('/songs/{id}/play', [SongRequestController::class, 'play']);


