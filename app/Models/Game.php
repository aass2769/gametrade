<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Game extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];

    public function servers(): HasMany
    {
        return $this->hasMany(GameServer::class);
    }

    public function items(): HasManyThrough
    {
        return $this->hasManyThrough(Item::class, GameServer::class, 'game_id', 'server_id');
    }

    public function imageUrl(): ?string
    {
        $images = config('games.images', []);
        $filename = is_array($images) ? ($images[$this->name] ?? null) : null;

        if (! is_string($filename) || $filename === '') {
            return null;
        }

        return asset('images/games/'.$filename);
    }
}
