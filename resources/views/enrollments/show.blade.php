@extends('layouts.app')
@section('title', 'Enrollment transaction')
@section('content')
    <a class="back-link" href="{{ route('enrollments.index') }}">← Back to enrollment history</a>
    <section class="panel transaction-detail">
        <p class="eyebrow">TRANSACTION #{{ $transaction->id }}</p>
        <h1>{{ ucfirst($transaction->type) }} · {{ $transaction->course->name }}</h1>
        <p class="muted">{{ $transaction->created_at->format('F j, Y · g:i a') }}</p>
        <dl class="detail-list">
            <div><dt>Student</dt><dd>{{ $transaction->user->name }} ({{ $transaction->user->email }})</dd></div>
            <div><dt>Course</dt><dd><a class="text-link" href="{{ route('courses.show', $transaction->course) }}">{{ $transaction->course->code }} · {{ $transaction->course->name }}</a></dd></div>
            <div><dt>Action</dt><dd>{{ ucfirst($transaction->type) }}</dd></div>
            <div><dt>Slot change</dt><dd>{{ $transaction->quantity > 0 ? '+' : '' }}{{ $transaction->quantity }}</dd></div>
            <div><dt>Reference</dt><dd>{{ $transaction->reference_number ?: '—' }}</dd></div>
            <div><dt>Notes</dt><dd>{{ $transaction->notes ?: '—' }}</dd></div>
            <div><dt>Current enrollment</dt><dd>{{ $isEnrolled ? 'Active' : 'Not active' }}</dd></div>
        </dl>
        <a class="button button-primary" href="{{ route('courses.show', $transaction->course) }}">Return to course</a>
    </section>
@endsection
