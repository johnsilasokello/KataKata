<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;

Route::get('/', [QuizController::class, 'index'])->name('home');
Route::get('/quiz', [QuizController::class, 'quiz'])->name('quiz');
Route::get('/leaderboard', [QuizController::class, 'leaderboard'])->name('leaderboard');
Route::post('/api/question', [QuizController::class, 'getQuestion'])->name('api.question');
Route::post('/api/explain', [QuizController::class, 'getExplanation'])->name('api.explain');
