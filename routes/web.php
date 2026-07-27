<?php

use App\Http\Controllers\BastController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PenawaranController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMoveController;
use App\Http\Controllers\ProjectStatusController;
use App\Http\Controllers\SpkController;
use App\Http\Controllers\TagihanMitraController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::patch('projects/{project}/move', ProjectMoveController::class)->name('projects.move');
    Route::patch('projects/{project}/status', [ProjectStatusController::class, 'update'])->name('projects.status.update');
    Route::get('projects/{project}/penawaran/create', [PenawaranController::class, 'create'])->name('projects.penawaran.create');
    Route::post('projects/{project}/penawaran', [PenawaranController::class, 'store'])->name('projects.penawaran.store');
    Route::get('projects/{project}/spk/create', [SpkController::class, 'create'])->name('projects.spk.create');
    Route::post('projects/{project}/spk', [SpkController::class, 'store'])->name('projects.spk.store');
    Route::get('projects/{project}/bast/create', [BastController::class, 'create'])->name('projects.bast.create');
    Route::post('projects/{project}/bast', [BastController::class, 'store'])->name('projects.bast.store');
    Route::get('projects/{project}/tagihan-mitra/create', [TagihanMitraController::class, 'create'])->name('projects.tagihan-mitra.create');
    Route::post('projects/{project}/tagihan-mitra', [TagihanMitraController::class, 'store'])->name('projects.tagihan-mitra.store');
});

require __DIR__.'/settings.php';
