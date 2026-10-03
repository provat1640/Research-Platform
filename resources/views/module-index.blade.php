<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $module }} · Co-Auth</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="app-shell"><aside class="sidebar"><a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark">C</span><span><strong>Co-Auth</strong><small>research studio</small></span></a><nav class="primary-nav" aria-label="Primary navigation"><a class="nav-item" href="{{ route('dashboard') }}"><span>◒</span>Overview</a><a class="nav-item" href="{{ route('tasks.index') }}"><span>✓</span>Task board</a><a class="nav-item" href="{{ route('feedback.index') }}"><span>↗</span>Feedback desk</a></nav><div class="sidebar-foot"><div class="status-dot"><span></span>Workspace online</div><p>Build a clearer thesis, together.</p></div></aside><main class="main-content"><header class="topbar"><div class="breadcrumbs"><a href="{{ route('dashboard') }}">Workspace</a><b>/</b><strong>{{ $module }}</strong></div><a class="button button-quiet" href="{{ route('dashboard') }}">← Overview</a></header><section class="welcome-row"><div><p class="eyebrow">{{ $eyebrow }}</p><h1>{{ $module }}<span>.</span></h1><p class="lede">{{ $description }}</p></div></section><section class="module-list">@forelse ($items as $item)<a class="module-item" href="{{ $item['url'] }}"><div><span class="module-status {{ $item['status'] === 'done' || $item['status'] === 'resolved' ? 'is-complete' : '' }}">{{ str_replace('_', ' ', $item['status']) }}</span><h2>{{ $item['title'] }}</h2><p>{{ $item['context'] }}</p></div><span class="module-meta">{{ $item['meta'] }}<b>→</b></span></a>@empty<div class="empty-state">Nothing here yet. Open a project workspace to add shared work.</div>@endforelse</section></main></div>
    </body>
</html>