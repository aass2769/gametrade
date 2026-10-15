<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameServer extends Model
{
    public $timestamps = false;

    protected $fillable = ['game_id', 'name'];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'server_id');
    }
}
