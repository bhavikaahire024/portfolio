<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth.session'])->group(function () {
    Route::get('/polls', [PollController::class, 'index'])->name('polls.index');
    Route::get('/polls/{poll}', [PollController::class, 'show'])->name('polls.show');
    Route::post('/polls/{poll}/vote', [PollController::class, 'vote'])->name('polls.vote');
    Route::get('/polls/{poll}/results', [PollController::class, 'results'])->name('polls.results');

    Route::get('/admin/polls/{poll}', [AdminController::class, 'show'])->name('admin.polls.show');
    Route::post('/admin/polls/{poll}/release', [AdminController::class, 'release'])->name('admin.polls.release');
    Route::get('/admin/polls/{poll}/history', [AdminController::class, 'history'])->name('admin.polls.history');
});
