<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubmissionController;

// ログインしているユーザーのみがアクセスできるルート
Route::middleware(['auth'])->group(function() {
    Route::post('/submissions', [SubmissionController::class, 'store']);
    Route::get('/submissions/{id}', [SubmissionController::class, 'show']);
    Route::get('/submissions/{id}/details', [SubmissionController::class, 'details']);
});
