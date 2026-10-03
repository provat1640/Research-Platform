<nav class="primary-nav" aria-label="Primary navigation">
    <a class="nav-item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}">
        <span>◒</span>Overview
    </a>
    @if (request()->routeIs('dashboard'))
        <a class="nav-item" href="#projects">
            <span>□</span>My projects
        </a>
    @endif
    <a class="nav-item {{ request()->routeIs('tasks.index') ? 'is-active' : '' }}" href="{{ route('tasks.index') }}">
        <span>✓</span>Task board
    </a>
    <a class="nav-item {{ request()->routeIs('feedback.index') ? 'is-active' : '' }}" href="{{ route('feedback.index') }}">
        <span>↗</span>Feedback desk
    </a>
</nav>
