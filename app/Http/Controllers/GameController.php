<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Yeni bir oyun başlatır.
     */
    public function startGame(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'surname' => 'required|string',
            'email' => 'required|email|unique:users,email',
        ]);

        $user = User::create($validated);

        $game = $user->games()->create([
            'score' => 0, // Başlangıç skoru
        ]);

        return response()->json([
            'message' => 'Game started',
            'game_id' => $game->id,
            'user_id' => $user->id
        ]);
    }

    /**
     * Oyun bitirildiğinde skoru ve sıralamayı döndürür.
     */
    public function endGame(Request $request)
    {
        $validated = $request->validate([
            'game_id' => 'required|exists:games,id',
            'user_id' => 'required|exists:users,id',
            'score' => 'required|integer|min:0|max:1000',
        ]);

        $game = Game::where('id', $validated['game_id'])->where('user_id', $validated['user_id'])->first();
        $game->score = $validated['score'];
        $game->save();

        $topUsers = Game::whereDate('created_at', now()->toDateString())
            ->orderByDesc('score')
            ->limit(10)
            ->get();

        $rank = $topUsers->search(fn($game) => $game->user_id === $validated['user_id']) + 1;

        return response()->json([
            'message' => 'Game ended',
            'user_rank' => $rank,
            'best_score' => $game->score
        ]);
    }

    /**
     * Günlük en iyi 10 kullanıcıyı listeleyen fonksiyon.
     */
    public function getTop10Users()
    {
        $topUsers = Game::whereDate('created_at', now()->toDateString())
            ->orderByDesc('score')
            ->limit(10)
            ->with('user')
            ->get()
            ->map(function ($game) {
                return [
                    'name' => $game->user->name,
                    'surname' => $game->user->surname,
                    'user_id' => $game->user->id,
                    'best_score' => $game->score,
                ];
            });

        return response()->json($topUsers);
    }
}
