<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->unsignedSmallInteger('server_id');
            $table->string('item_name', 100);
            $table->unsignedInteger('price');
            $table->unsignedInteger('quantity');
            $table->text('description')->nullable();
            $table->enum('status', ['ON_SALE', 'IN_PROGRESS', 'COMPLETED'])->default('ON_SALE');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('created_at');
            $table->index('price');
            $table->index('status');
            $table->foreign('server_id')->references('id')->on('game_servers')->restrictOnDelete()->cascadeOnUpdate();
        });

        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE items ADD CONSTRAINT chk_items_price CHECK (price >= 1 AND price <= 100000000)');
            DB::statement('ALTER TABLE items ADD CONSTRAINT chk_items_quantity CHECK (quantity >= 1 AND quantity <= 9999)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
