<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $fillable = [
        'spotify_id',
        'title',
        'artist',
        'uri',
        'album',
        'album_image',
        'release_year',
    ];

}
