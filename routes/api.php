<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\ProjectFeedbackController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\ResearchProjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/projects', [ResearchProjectController::class, 'index']);
    Route::post('/projects', [ResearchProjectController::class, 'store']);
    Route::get('/projects/{project}', [ResearchProjectController::class, 'show']);
    Route::post('/projects/{project}/versions', [ResearchProjectController::class, 'storeVersion']);
    Route::get('/projects/{project}/tasks', [ProjectTaskController::class, 'index']);
    Route::post('/projects/{project}/tasks', [ProjectTaskController::class, 'store']);
    Route::patch('/tasks/{task}', [ProjectTaskController::class, 'update']);
    Route::get('/projects/{project}/feedback', [ProjectFeedbackController::class, 'index']);
    Route::post('/projects/{project}/feedback', [ProjectFeedbackController::class, 'store']);
    Route::patch('/feedback/{feedback}', [ProjectFeedbackController::class, 'update']);
    Route::post('/projects/{project}/ai/summary', [AiController::class, 'summarize']);
});
