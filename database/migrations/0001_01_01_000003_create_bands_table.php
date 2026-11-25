<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bands', static function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('genre', 32)->index('bands_genre_index');
            $table->unsignedSmallInteger('formed_in');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bands');
    }
};
