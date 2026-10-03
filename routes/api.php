<?php

use App\Http\Controllers\ResearchProjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/projects', [ResearchProjectController::class, 'index']);
    Route::post('/projects', [ResearchProjectController::class, 'store']);
    Route::get('/projects/{project}', [ResearchProjectController::class, 'show']);
    Route::post('/projects/{project}/versions', [ResearchProjectController::class, 'storeVersion']);
});
