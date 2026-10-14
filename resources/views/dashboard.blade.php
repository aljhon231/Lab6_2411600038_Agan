@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <section class="welcome-banner" data-dashboard-stats="{{ route('api.dashboard.stats') }}" data-almost-full="{{ route('api.courses.almost-full') }}" data-recent="{{ route('api.enrollments.recent') }}">
        <div><p class="eyebrow">STUDENT PORTAL · {{ now()->format('l, F j') }}</p><h1>Welcome back, {{ str(auth()->user()->name)->before(' ') }}.</h1><p>Your learning journey, all in one place.</p></div>
        <a class="button button-light" href="{{ route('courses.index') }}">Explore courses <span aria-hidden="true">→</span></a>
    </section>

    <section class="stat-grid" aria-label="Portal statistics">
        <article class="stat-card"><span class="stat-icon icon-blue">01</span><span class="stat-label">Total students</span><strong id="stat-students">—</strong><small>Registered student accounts</small></article>
        <article class="stat-card"><span class="stat-icon icon-violet">02</span><span class="stat-label">Total courses</span><strong id="stat-courses">—</strong><small>In the course catalog</small></article>
        <article class="stat-card"><span class="stat-icon icon-green">03</span><span class="stat-label">Active enrollments</span><strong id="stat-enrollments">—</strong><small>Students currently enrolled</small></article>
        <article class="stat-card"><span class="stat-icon icon-amber">04</span><span class="stat-label">Almost full</span><strong id="stat-almost-full">—</strong><small>Courses near their capacity</small></article>
    </section>

    <div class="dashboard-grid">
        <section class="panel">
            <div class="section-heading"><div><p class="eyebrow">SEAT AVAILABILITY</p><h2>Courses almost full</h2></div><a class="text-link" href="{{ route('courses.index', ['status' => 'almost-full']) }}">See all →</a></div>
            <div id="almost-full-list" class="alert-course-list">
                @forelse($almostFullCourses as $course)
                    <a class="alert-course" href="{{ route('courses.show', $course) }}"><span class="alert-dot"></span><span><strong>{{ $course->name }}</strong><small>{{ $course->code }}</small></span><b>{{ $course->available_slots }} {{ \Illuminate\Support\Str::plural('slot', $course->available_slots) }}</b></a>
                @empty
                    <div class="empty-inline">No courses are close to full right now.</div>
                @endforelse
            </div>
        </section>
        <section class="panel">
            <div class="section-heading"><div><p class="eyebrow">YOUR SHORTCUTS</p><h2>Quick actions</h2></div></div>
            <div class="quick-links">
                <a href="{{ route('enrollments.create') }}"><span class="quick-icon">＋</span><span><strong>Enroll in a course</strong><small>Find a subject with open seats</small></span><b>→</b></a>
                <a href="{{ route('enrollments.index') }}"><span class="quick-icon">↗</span><span><strong>Enrollment history</strong><small>Review recent course activity</small></span><b>→</b></a>
                <a href="{{ route('reports.index') }}"><span class="quick-icon">▤</span><span><strong>Course reports</strong><small>Browse availability and history</small></span><b>→</b></a>
                @can('manage-portal')
                    <a href="{{ route('courses.create') }}"><span class="quick-icon">✳</span><span><strong>Add a course</strong><small>Manage the course catalog</small></span><b>→</b></a>
                @endcan
            </div>
        </section>
    </div>

    <section class="panel recent-panel">
        <div class="section-heading"><div><p class="eyebrow">LATEST ACTIVITY</p><h2>Recent enrollment transactions</h2></div><a class="text-link" href="{{ route('enrollments.index') }}">View history →</a></div>
        <div id="recent-enrollments-table">@include('enrollments.transaction-table', ['transactions' => $recentTransactions])</div>
        <p class="poll-status" id="poll-status">Dashboard refreshes every 30 seconds.</p>
    </section>
@endsection

@push('styles')
    <script src="{{ asset('js/dashboard.js') }}" defer></script>
@endpush
