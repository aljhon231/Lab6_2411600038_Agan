@extends('layouts.app')
@section('title', 'Reports')
@section('content')
    <section class="page-heading"><div><p class="eyebrow">ACADEMIC OVERVIEW</p><h1>Course reports</h1><p class="muted">Enrollment totals, available seats, and a filterable transaction record.</p></div></section>
    <section class="report-grid">
        <article class="panel report-panel"><div class="section-heading"><div><p class="eyebrow">ENROLLMENT REPORT</p><h2>Students per course</h2></div></div><div class="table-wrap"><table><thead><tr><th>Course</th><th>Category</th><th>Enrolled</th></tr></thead><tbody>@forelse($courses as $course)<tr><td><a class="text-link" href="{{ route('courses.show', $course) }}">{{ $course->code }} · {{ $course->name }}</a></td><td>{{ $course->category }}</td><td>{{ $course->enrollments_count }}</td></tr>@empty<tr><td colspan="3" class="table-empty">No courses found.</td></tr>@endforelse</tbody></table></div></article>
        <article class="panel report-panel"><div class="section-heading"><div><p class="eyebrow">COURSE AVAILABILITY</p><h2>Seats and status</h2></div></div><div class="table-wrap"><table><thead><tr><th>Course</th><th>Available</th><th>Status</th></tr></thead><tbody>@forelse($courses as $course)<tr><td>{{ $course->name }}</td><td>{{ $course->available_slots }} / {{ $course->maximum_slots }}</td><td><span class="status-badge status-{{ str($course->enrollment_status)->slug() }}">{{ $course->enrollment_status }}</span></td></tr>@empty<tr><td colspan="3" class="table-empty">No courses found.</td></tr>@endforelse</tbody></table></div></article>
    </section>
    <section class="panel report-transactions">
        <div class="section-heading"><div><p class="eyebrow">ENROLLMENT TRANSACTION REPORT</p><h2>Activity history</h2></div></div>
        <form class="filter-bar report-filters" method="get">
            <select name="course_id" aria-label="Filter by course"><option value="">All courses</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected(request('course_id') == $course->id)>{{ $course->code }} · {{ $course->name }}</option>@endforeach</select>
            <select name="type" aria-label="Filter by action"><option value="">All actions</option>@foreach(['enroll', 'cancel', 'adjustment'] as $type)<option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>@endforeach</select>
            <label class="date-filter"><span>From</span><input type="date" name="from" value="{{ request('from') }}"></label>
            <label class="date-filter"><span>To</span><input type="date" name="to" value="{{ request('to') }}"></label>
            <button class="button button-dark" type="submit">Filter report</button>
        </form>
        @include('enrollments.transaction-table', ['transactions' => $transactions])
        <div class="pagination-wrap">{{ $transactions->links() }}</div>
    </section>
@endsection
