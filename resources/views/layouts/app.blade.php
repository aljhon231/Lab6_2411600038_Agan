<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student Portal') · Student Portal</title>
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    @stack('styles')
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('dashboard') }}">
            <span class="brand-mark">S</span>
            <span>Student Portal<small>Learning, organized.</small></span>
        </a>
        <button class="menu-toggle" type="button" aria-label="Open navigation" onclick="document.body.classList.toggle('nav-open')">☰</button>
        <nav class="main-nav">
            <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="{{ request()->routeIs('courses.*') ? 'active' : '' }}" href="{{ route('courses.index') }}">Courses</a>
            <a class="{{ request()->routeIs('enrollments.*') ? 'active' : '' }}" href="{{ route('enrollments.index') }}">Enrollment history</a>
            <a class="{{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">Reports</a>
        </nav>
        <div class="account-nav">
            <details class="notification-menu">
                <summary aria-label="Notifications">
                    <span class="bell">♧</span>
                    @if(auth()->user()->unreadNotifications()->count())
                        <span class="notification-count">{{ auth()->user()->unreadNotifications()->count() }}</span>
                    @endif
                </summary>
                <div class="notification-popover">
                    <strong>Few-slot alerts</strong>
                    @forelse(auth()->user()->unreadNotifications()->latest()->limit(5)->get() as $notification)
                        <p>{{ $notification->data['message'] ?? 'Course availability alert.' }}</p>
                    @empty
                        <p class="muted">You are all caught up.</p>
                    @endforelse
                    @if(auth()->user()->unreadNotifications()->exists())
                        <form method="post" action="{{ route('notifications.read') }}">
                            @csrf
                            <button class="text-button" type="submit">Mark all as read</button>
                        </form>
                    @endif
                </div>
            </details>
            <span class="account-name">{{ auth()->user()->name }}{{ auth()->user()->isAdmin() ? ' · Admin' : '' }}</span>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button class="button button-quiet button-small" type="submit">Log out</button>
            </form>
        </div>
    </header>

    <div class="mobile-overlay" onclick="document.body.classList.remove('nav-open')"></div>
    <main class="page-shell">
        @if(session('success'))
            <div class="flash flash-success" role="status">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash flash-error" role="alert">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="flash flash-error" role="alert">
                <strong>Please check the form.</strong>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @yield('content')
    </main>
    <footer class="site-footer">Student Portal Management System <span>·</span> Lab 6</footer>
</body>
</html>
