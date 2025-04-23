<?php

use App\Services\QueueSyncService;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    app(QueueSyncService::class)->sync();
})->everyFifteenSeconds();

