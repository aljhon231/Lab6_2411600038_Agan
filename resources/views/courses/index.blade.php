@extends('layouts.app')

@section('title', 'Courses')

@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">YOUR LEARNING OPTIONS</p>
            <h1>Course catalog</h1>
            <p class="muted">Find a course and check its current seat availability.</p>
        </div>
        @can('manage-portal')
            <a class="button button-primary" href="{{ route('courses.create') }}">＋ Add course</a>
        @endcan
    </section>

    <form class="filter-bar" method="get" action="{{ route('courses.index') }}">
        <label class="sr-only" for="course-search">Search courses</label>
        <input id="course-search" type="search" name="search" value="{{ request('search') }}" placeholder="Search name, code, or category">
        <select name="status" aria-label="Filter by availability">
            <option value="">All availability</option>
            <option value="available" @selected(request('status') === 'available')>Available</option>
            <option value="almost-full" @selected(request('status') === 'almost-full')>Almost full</option>
            <option value="full" @selected(request('status') === 'full')>Full</option>
        </select>
        <button class="button button-dark" type="submit">Apply filters</button>
        @if(request()->hasAny(['search', 'status']))
            <a class="button button-quiet" href="{{ route('courses.index') }}">Clear</a>
        @endif
    </form>

    <div class="course-grid">
        @forelse($courses as $course)
            <article class="course-card">
                <div class="course-card-top">
                    <span class="course-code">{{ $course->code }}</span>
                    <span class="status-badge status-{{ str($course->enrollment_status)->slug() }}">{{ $course->enrollment_status }}</span>
                </div>
                <p class="course-category">{{ $course->category }}</p>
                <h2><a href="{{ route('courses.show', $course) }}">{{ $course->name }}</a></h2>
                <p class="course-instructor">{{ $course->instructor ?: 'Instructor to be announced' }}</p>
                <div class="availability-row">
                    <span><strong>{{ $course->available_slots }}</strong> slots available</span>
                    <span>of {{ $course->maximum_slots }}</span>
                </div>
                <div class="slot-track"><span style="width: {{ $course->maximum_slots ? min(100, max(0, (($course->maximum_slots - $course->available_slots) / $course->maximum_slots) * 100)) : 0 }}%"></span></div>
                <a class="card-link" href="{{ route('courses.show', $course) }}">View course <span aria-hidden="true">→</span></a>
            </article>
        @empty
            <div class="empty-state"><strong>No courses found</strong><p>Try changing your search or filters.</p></div>
        @endforelse
    </div>
    <div class="pagination-wrap">{{ $courses->links() }}</div>
@endsection
