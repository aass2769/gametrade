<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];

    public function servers(): HasMany
    {
        return $this->hasMany(GameServer::class);
    }
}
