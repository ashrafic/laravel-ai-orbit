<?php

use Ashrafic\AiOrbit\Http\Controllers\ArenaController;
use Ashrafic\AiOrbit\Http\Controllers\AuditController;
use Ashrafic\AiOrbit\Http\Controllers\ConversationController;
use Ashrafic\AiOrbit\Http\Controllers\DashboardController;
use Ashrafic\AiOrbit\Http\Controllers\ExportController;
use Ashrafic\AiOrbit\Http\Controllers\PlaygroundController;
use Ashrafic\AiOrbit\Http\Controllers\PromptController;
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

Route::get('/arena', [ArenaController::class, 'index'])->name('orbit.arena.index');
Route::get('/arena/session/{id}', [ArenaController::class, 'show'])->name('orbit.arena.show');

Route::get('/usage/dashboard', [UsageController::class, 'dashboard'])->name('orbit.usage.dashboard');
Route::get('/usage/pricing', [UsageController::class, 'pricing'])->name('orbit.usage.pricing');
Route::get('/usage/alerts', [UsageController::class, 'alerts'])->name('orbit.usage.alerts');
Route::get('/usage/health', [UsageController::class, 'health'])->name('orbit.usage.health');

Route::get('/audit', [AuditController::class, 'index'])->name('orbit.audit.index');

Route::post('/export/pest/{id}', [ExportController::class, 'pest'])->name('orbit.export.pest');
Route::post('/export/json/{id}', [ExportController::class, 'json'])->name('orbit.export.json');

Route::get('/prompts', [PromptController::class, 'index'])->name('orbit.prompts.index');
