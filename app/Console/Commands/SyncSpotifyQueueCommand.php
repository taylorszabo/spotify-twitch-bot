<?php

namespace App\Console\Commands;

use App\Services\QueueSyncService;
use Illuminate\Console\Command;

class SyncSpotifyQueueCommand extends Command
{
    protected $signature = 'sync:spotify-queue';

    protected $description = 'Command description';

    public function handle(): void
    {
        app(QueueSyncService::class)->sync();
    }
}
