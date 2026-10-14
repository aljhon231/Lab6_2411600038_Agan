@extends('layouts.app')
@section('title', 'Enrollment history')
@section('content')
    <section class="page-heading"><div><p class="eyebrow">YOUR COURSE ACTIVITY</p><h1>Enrollment history</h1><p class="muted">{{ auth()->user()->isAdmin() ? 'All recorded course activity.' : 'Your enrollment and cancellation activity.' }}</p></div><a class="button button-primary" href="{{ route('enrollments.create') }}">＋ Find a course</a></section>
    @can('manage-portal')
        <form class="filter-bar compact-filter" method="get"><label class="sr-only" for="type">Filter by action</label><select id="type" name="type"><option value="">All actions</option>@foreach(['enroll', 'cancel', 'adjustment'] as $type)<option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>@endforeach</select><button class="button button-dark" type="submit">Filter</button></form>
    @endcan
    <section class="panel">
        @include('enrollments.transaction-table', ['transactions' => $transactions])
        <div class="pagination-wrap">{{ $transactions->links() }}</div>
    </section>
@endsection
