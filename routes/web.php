<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FeedItemController;
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
    Route::get('dashboard1', [DashboardController::class, 'themeOne'])->name('dashboard1');
    Route::get('dashboard2', [DashboardController::class, 'themeTwo'])->name('dashboard2');
    Route::get('dashboard3', [DashboardController::class, 'themeThree'])->name('dashboard3');
    Route::get('dashboard4', [DashboardController::class, 'themeFour'])->name('dashboard4');
    Route::get('dashboard5', [DashboardController::class, 'themeFive'])->name('dashboard5');
    Route::post('feeds', [FeedController::class, 'store'])->name('feeds.store');
    Route::post('feeds/{feed}/refresh', [FeedController::class, 'refresh'])->name('feeds.refresh');
    Route::delete('feeds/{feed}', [FeedController::class, 'destroy'])->name('feeds.destroy');
    Route::get('feed-items/{feedItem}', [FeedItemController::class, 'show'])->name('feed-items.show');
});

require __DIR__.'/settings.php';
