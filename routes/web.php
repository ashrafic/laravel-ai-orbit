<?php

use Ashrafic\AiOrbit\Http\Controllers\ConversationController;
use Ashrafic\AiOrbit\Http\Controllers\DashboardController;
use Ashrafic\AiOrbit\Http\Controllers\PlaygroundController;
use Ashrafic\AiOrbit\Http\Controllers\TraceController;
use Ashrafic\AiOrbit\Http\Controllers\UsageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('orbit.dashboard');
Route::get('/conversations', [ConversationController::class, 'index'])->name('orbit.conversations.index');
Route::get('/conversations/{id}', [ConversationController::class, 'show'])->name('orbit.conversations.show');
Route::get('/playground', [PlaygroundController::class, 'index'])->name('orbit.playground.index');
Route::get('/playground/{agent}', [PlaygroundController::class, 'show'])->name('orbit.playground.show');
Route::get('/traces/{id}', [TraceController::class, 'show'])->name('orbit.traces.show');
Route::get('/usage', [UsageController::class, 'index'])->name('orbit.usage.index');
