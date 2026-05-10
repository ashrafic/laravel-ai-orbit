<?php

use Ashraf\LaravelAiOrbit\Http\Controllers\ConversationController;
use Ashraf\LaravelAiOrbit\Http\Controllers\DashboardController;
use Ashraf\LaravelAiOrbit\Http\Controllers\PlaygroundController;
use Ashraf\LaravelAiOrbit\Http\Controllers\TraceController;
use Ashraf\LaravelAiOrbit\Http\Controllers\UsageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('orbit.dashboard');
Route::get('/conversations', [ConversationController::class, 'index'])->name('orbit.conversations.index');
Route::get('/conversations/{id}', [ConversationController::class, 'show'])->name('orbit.conversations.show');
Route::get('/playground', [PlaygroundController::class, 'index'])->name('orbit.playground.index');
Route::get('/playground/{agent}', [PlaygroundController::class, 'show'])->name('orbit.playground.show');
Route::get('/traces/{id}', [TraceController::class, 'show'])->name('orbit.traces.show');
Route::get('/usage', [UsageController::class, 'index'])->name('orbit.usage.index');
