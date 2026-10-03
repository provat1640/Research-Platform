<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\IntegrationHealthController;
use App\Http\Controllers\PaperController;
use App\Http\Controllers\ProjectFeedbackController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\ResearchProjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth')->group(function (): void {
    Route::get('/projects', [ResearchProjectController::class, 'index']);
    Route::post('/projects', [ResearchProjectController::class, 'store']);
    Route::get('/integrations/health', IntegrationHealthController::class);
    Route::middleware('project.member')->group(function (): void {
        Route::get('/projects/{project}', [ResearchProjectController::class, 'show']);
        Route::post('/projects/{project}/versions', [ResearchProjectController::class, 'storeVersion']);
        Route::patch('/projects/{project}/setup', [ResearchProjectController::class, 'updateSetup']);
        Route::get('/projects/{project}/tasks', [ProjectTaskController::class, 'index']);
        Route::post('/projects/{project}/tasks', [ProjectTaskController::class, 'store']);
        Route::get('/projects/{project}/feedback', [ProjectFeedbackController::class, 'index']);
        Route::post('/projects/{project}/feedback', [ProjectFeedbackController::class, 'store']);
        Route::post('/projects/{project}/ai/summary', [AiController::class, 'summarize'])->middleware('ai.access');
        Route::get('/projects/{project}/papers', [PaperController::class, 'index']);
        Route::post('/projects/{project}/papers', [PaperController::class, 'store']);
    });
    Route::patch('/tasks/{task}', [ProjectTaskController::class, 'update']);
    Route::patch('/feedback/{feedback}', [ProjectFeedbackController::class, 'update']);
});
