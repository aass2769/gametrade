<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    public function index(): JsonResponse
    {
        $games = Game::query()
            ->with('servers')
            ->orderBy('id')
            ->get()
            ->map(fn (Game $game) => [
                'id' => $game->id,
                'name' => $game->name,
                'servers' => $game->servers->map(fn ($server) => [
                    'id' => $server->id,
                    'name' => $server->name,
                ])->values(),
            ]);

        return response()->json(['data' => $games]);
    }
}
