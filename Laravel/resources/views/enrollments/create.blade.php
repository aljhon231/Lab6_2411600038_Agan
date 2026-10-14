@extends('layouts.app')
@section('title', 'Enroll in a course')
@section('content')
    <a class="back-link" href="{{ route('courses.index') }}">← Browse courses</a>
    <section class="page-heading"><div><p class="eyebrow">START LEARNING</p><h1>Enroll in a course</h1><p class="muted">Choose an available course. Your seat is reserved as soon as you enroll.</p></div></section>
    <form class="panel form-panel enrollment-form" method="post" action="{{ route('enrollments.store') }}">
        @csrf
        @if($courses->isEmpty())
            <div class="empty-state"><strong>No courses with available seats</strong><p>Check back later or browse the catalog.</p><a class="button button-quiet" href="{{ route('courses.index') }}">View all courses</a></div>
        @else
            <div class="field">
                <label for="course_id">Course</label>
                <select id="course_id" name="course_id" required>
                    <option value="">Select a course</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected(old('course_id', request('course_id')) == $course->id)>{{ $course->code }} · {{ $course->name }} — {{ $course->available_slots }} slots ({{ $course->enrollment_status }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-actions"><a class="button button-quiet" href="{{ route('courses.index') }}">Cancel</a><button class="button button-primary" type="submit">Confirm enrollment</button></div>
        @endif
    </form>
@endsection
