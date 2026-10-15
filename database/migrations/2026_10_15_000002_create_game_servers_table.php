<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_servers', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('game_id');
            $table->string('name', 50);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['game_id', 'name']);
            $table->foreign('game_id')->references('id')->on('games')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_servers');
    }
};
