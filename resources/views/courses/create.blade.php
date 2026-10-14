@extends('layouts.app')
@section('title', 'Add course')
@section('content')
    <section class="page-heading"><div><p class="eyebrow">COURSE MANAGEMENT</p><h1>Add a course</h1><p class="muted">Set up a subject and its available seats.</p></div></section>
    <form class="panel form-panel" method="post" action="{{ route('courses.store') }}">
        @csrf
        @include('courses.form')
        <div class="form-actions"><a class="button button-quiet" href="{{ route('courses.index') }}">Cancel</a><button class="button button-primary" type="submit">Create course</button></div>
    </form>
@endsection
