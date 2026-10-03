<?php

use App\Models\ResearchProject;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

use App\Models\ProjectFeedback;
use App\Models\ProjectTask;

Route::get('/projects/{project}', function (ResearchProject $project) {
    return view('project-workspace', ['project' => $project]);
})->name('projects.show');

Route::get('/tasks', function () {
    return view('module-index', [
        'module' => 'Task board',
        'eyebrow' => 'Shared work',
        'description' => 'Turn research decisions into visible next steps for the whole team.',
        'items' => ProjectTask::with('project:id,title')->latest()->get()->map(fn (ProjectTask $task): array => [
            'title' => $task->title,
            'context' => $task->project->title,
            'status' => $task->status,
            'meta' => $task->priority.' priority',
            'url' => route('projects.show', $task->project),
        ]),
    ]);
})->name('tasks.index');

Route::get('/feedback', function () {
    return view('module-index', [
        'module' => 'Feedback desk',
        'eyebrow' => 'Review loop',
        'description' => 'Keep supervisor notes, responses, and resolved decisions in one trail.',
        'items' => ProjectFeedback::with(['project:id,title', 'author:id,name'])->latest()->get()->map(fn (ProjectFeedback $feedback): array => [
            'title' => $feedback->body,
            'context' => $feedback->project->title,
            'status' => $feedback->status,
            'meta' => $feedback->author->name,
            'url' => route('projects.show', $feedback->project),
        ]),
    ]);
})->name('feedback.index');
