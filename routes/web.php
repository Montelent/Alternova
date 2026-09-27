<?php

use App\Http\Controllers\InstallController;
use App\Livewire\AlternativeDetail;
use App\Livewire\DomainCombinator;
use App\Livewire\OpenSourceFinder;
use App\Support\Installer;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Installer
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'not.installed'])->prefix('install')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('install.index');
    Route::post('/next', [InstallController::class, 'next'])->name('install.next');
    Route::post('/back', [InstallController::class, 'back'])->name('install.back');
    Route::post('/recheck', [InstallController::class, 'recheck'])->name('install.recheck');
});

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/alternatives', OpenSourceFinder::class)->name('finder');
Route::get('/alternatives/{alternative:slug}', AlternativeDetail::class)->name('alternatives.show');
Route::get('/domains', DomainCombinator::class)->name('domains');
