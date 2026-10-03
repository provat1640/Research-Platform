<?php

use App\Models\ResearchProject;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/projects/{project}', function (ResearchProject $project) {
    return view('project-workspace', ['project' => $project]);
})->name('projects.show');
