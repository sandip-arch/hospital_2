<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AI\AIChatController;



Route::middleware(['auth'])->prefix('ai')->name('ai.')->group(function () {
    Route::post('/chat', [AIChatController::class, 'chat'])->name('chat');
    Route::get('/history', [AIChatController::class, 'history'])->name('history');
    Route::get('/conversation/{id}', [AIChatController::class, 'showConversation'])->name('conversation.show');
    Route::post('/conversation/clear', [AIChatController::class, 'clearConversation'])->name('conversation.clear');
    Route::get('/prompts', [AIChatController::class, 'quickPrompts'])->name('prompts');
});
