<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Song;
use Inertia\Inertia;
use Inertia\Response;

class SongAdminController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/SongPanel', [
            'songs' => Song::orderByDesc('created_at')->get(),
        ]);
    }
}
