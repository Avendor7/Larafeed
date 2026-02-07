<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('feeds', [FeedController::class, 'store'])->name('feeds.store');
    Route::post('feeds/{feed}/refresh', [FeedController::class, 'refresh'])->name('feeds.refresh');
    Route::delete('feeds/{feed}', [FeedController::class, 'destroy'])->name('feeds.destroy');
});

require __DIR__.'/settings.php';
