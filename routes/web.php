<?php

use App\Http\Controllers\AuthController;
use App\Models\ProjectFeedback;
use App\Models\ProjectTask;
use App\Models\ResearchProject;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'store'])->middleware('guest')->name('login.store');
Route::get('/register', [AuthController::class, 'register'])->middleware('guest')->name('register');
Route::post('/register', [AuthController::class, 'storeRegistration'])->middleware('guest')->name('register.store');
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/', fn () => view('dashboard'));
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::get('/projects/{project}', fn (ResearchProject $project) => view('project-workspace', ['project' => $project]))->middleware('project.member')->name('projects.show');

    Route::get('/tasks', function () {
        $user = auth()->user();

        return view('module-index', [
            'module' => 'Task board',
            'eyebrow' => 'Shared work',
            'description' => 'Turn research decisions into visible next steps for the whole team.',
            'items' => ProjectTask::query()
                ->when($user, function ($query, $user) {
                    $query->whereHas('project', function ($q) use ($user) {
                        $q->where('owner_id', $user->id)
                            ->orWhereHas('members', fn ($memberQuery) => $memberQuery->where('users.id', $user->id));
                    });
                })
                ->with('project:id,title')
                ->latest()
                ->get()
                ->map(fn (ProjectTask $task): array => [
                    'title' => $task->title,
                    'context' => $task->project->title,
                    'status' => $task->status,
                    'meta' => $task->priority.' priority',
                    'url' => route('projects.show', $task->project),
                ]),
        ]);
    })->name('tasks.index');

    Route::get('/feedback', function () {
        $user = auth()->user();

        return view('module-index', [
            'module' => 'Feedback desk',
            'eyebrow' => 'Review loop',
            'description' => 'Keep supervisor notes, responses, and resolved decisions in one trail.',
            'items' => ProjectFeedback::query()
                ->when($user, function ($query, $user) {
                    $query->whereHas('project', function ($q) use ($user) {
                        $q->where('owner_id', $user->id)
                            ->orWhereHas('members', fn ($memberQuery) => $memberQuery->where('users.id', $user->id));
                    });
                })
                ->with(['project:id,title', 'author:id,name'])
                ->latest()
                ->get()
                ->map(fn (ProjectFeedback $feedback): array => [
                    'title' => $feedback->body,
                    'context' => $feedback->project->title,
                    'status' => $feedback->status,
                    'meta' => $feedback->author->name,
                    'url' => route('projects.show', $feedback->project),
                ]),
        ]);
    })->name('feedback.index');
});
