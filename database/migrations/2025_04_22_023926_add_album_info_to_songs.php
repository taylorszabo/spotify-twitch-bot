<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
// database/migrations/xxxx_add_album_info_to_songs_table.php

    public function up()
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->string('album')->nullable();
            $table->string('album_image')->nullable();
            $table->string('release_year')->nullable();
        });
    }

};
