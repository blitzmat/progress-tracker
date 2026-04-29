<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', \App\Livewire\Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    Route::get('goals', \App\Livewire\Goals::class)
        ->name('goals.index');
    Route::get('activities', \App\Livewire\Activities::class)
        ->name('activities.index');
});
require __DIR__.'/auth.php';
