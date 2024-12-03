<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::post('start-game', [GameController::class, 'startGame']);
Route::post('end-game', [GameController::class, 'endGame']);
Route::get('top-10-users', [GameController::class, 'getTop10Users']);
