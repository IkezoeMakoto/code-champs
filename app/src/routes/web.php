<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\SubmissionController;

Route::get('/register', function () {
    return view('register');
});
Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('challenges.index');
    }
    return redirect()->route('login');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);

// ログインしていないユーザーはログイン画面にリダイレクト
Route::middleware(['auth'])->group(function () {
    // チャレンジ
    Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenges.index');
    Route::get('/challenge/{id}', [ChallengeController::class, 'show'])->name('challenge.show');
    // チャレンジのランキング
    Route::get('/challenges/{id}/ranking', [ChallengeController::class, 'ranking'])->name('challenges.ranking');
    // チャレンジの提出
    Route::post('/submissions', [SubmissionController::class, 'store'])->name('submissions.store');

    // 提出コード取得API (RESTfulなURL形式に変更)
    Route::get('/challenges/{id}/submissions/{submission_id}', [ChallengeController::class, 'getSubmissionCode'])->name('challenges.submissions.show');

    // 管理者のみがアクセスできるルート
    Route::middleware(['can:create,App\\Models\\Challenge'])->group(function () {
        Route::get('/challenges/create', [ChallengeController::class, 'create'])->name('challenges.create');
        Route::post('/challenges', [ChallengeController::class, 'store'])->name('challenges.store');
        Route::put('/challenges/{id}', [ChallengeController::class, 'update'])->name('challenges.update');
        Route::get('/challenges/{id}/edit', [ChallengeController::class, 'edit'])->name('challenges.edit');
        Route::delete('/challenges/{id}', [ChallengeController::class, 'destroy'])->name('challenges.destroy');
    });

});
