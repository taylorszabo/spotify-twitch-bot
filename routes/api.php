<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SongRequestController;

Route::middleware('api')->post('/songs', [SongRequestController::class, 'store']);
