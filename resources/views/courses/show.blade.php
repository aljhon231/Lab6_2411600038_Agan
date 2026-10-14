@extends('layouts.app')
@section('title', $course->name)
@section('content')
    <a class="back-link" href="{{ route('courses.index') }}">← Back to courses</a>
    <section class="course-detail panel">
        <div class="detail-heading">
            <div><p class="eyebrow">{{ $course->category }} · {{ $course->code }}</p><h1>{{ $course->name }}</h1><p class="muted">{{ $course->instructor ?: 'Instructor to be announced' }}</p></div>
            <span class="status-badge status-{{ str($course->enrollment_status)->slug() }}">{{ $course->enrollment_status }}</span>
        </div>
        @if($course->description)<p class="course-description">{{ $course->description }}</p>@endif
        <div class="detail-stats">
            <div><strong>{{ $course->available_slots }}</strong><span>Available seats</span></div>
            <div><strong>{{ $course->maximum_slots }}</strong><span>Maximum seats</span></div>
            <div><strong>{{ $course->enrollments_count }}</strong><span>Enrolled students</span></div>
            <div><strong>{{ $course->minimum_slots }}</strong><span>Alert threshold</span></div>
        </div>
        <div class="detail-actions">
            @if($isEnrolled)
                <span class="enrolled-label">✓ You are enrolled</span>
                <form method="post" action="{{ route('courses.cancel', $course) }}" onsubmit="return confirm('Cancel your enrollment in this course?')">
                    @csrf
                    <button class="button button-danger" type="submit">Cancel enrollment</button>
                </form>
            @elseif($course->hasAvailableSlots())
                <form method="post" action="{{ route('enrollments.store') }}">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $course->id }}">
                    <button class="button button-primary" type="submit">Enroll in this course</button>
                </form>
            @else
                <span class="muted">This course is full right now.</span>
            @endif
            @can('manage-portal')
                <a class="button button-quiet" href="{{ route('courses.edit', $course) }}">Edit course</a>
                @if($canDelete)
                    <form method="post" action="{{ route('courses.destroy', $course) }}" onsubmit="return confirm('Delete this course? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button class="button button-danger" type="submit">Delete course</button>
                    </form>
                @endif
            @endcan
        </div>
    </section>

    @can('manage-portal')
        <section class="panel adjustment-panel">
            <div class="section-heading"><div><p class="eyebrow">ADMINISTRATION</p><h2>Adjust available slots</h2></div><span class="muted">Every change is recorded.</span></div>
            <form class="adjustment-form" method="post" action="{{ route('courses.adjust', $course) }}">
                @csrf
                <div class="field"><label for="quantity">Slot change</label><input id="quantity" name="quantity" type="number" min="-{{ $course->maximum_slots }}" max="{{ $course->maximum_slots }}" step="1" required placeholder="e.g. 5 or -2"></div>
                <div class="field"><label for="reference_number">Reference number <span class="muted">(optional)</span></label><input id="reference_number" name="reference_number" maxlength="100"></div>
                <div class="field field-wide"><label for="notes">Reason for adjustment</label><input id="notes" name="notes" required maxlength="1000" placeholder="Explain why the slot count changed"></div>
                <button class="button button-dark" type="submit">Record adjustment</button>
            </form>
        </section>
    @endcan

    <section class="panel">
        <div class="section-heading"><div><p class="eyebrow">COURSE ACTIVITY</p><h2>Recent enrollment history</h2></div><a class="text-link" href="{{ route('enrollments.index') }}">View all →</a></div>
        @include('enrollments.transaction-table', ['transactions' => $recentTransactions])
    </section>
@endsection
