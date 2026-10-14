@extends('layouts.app')
@section('title', 'Edit course')
@section('content')
    <section class="page-heading"><div><p class="eyebrow">COURSE MANAGEMENT</p><h1>Edit {{ $course->name }}</h1><p class="muted">Update course information and its maximum capacity.</p></div></section>
    <form class="panel form-panel" method="post" action="{{ route('courses.update', $course) }}">
        @csrf
        @method('put')
        @include('courses.form')
        <div class="form-actions"><a class="button button-quiet" href="{{ route('courses.show', $course) }}">Cancel</a><button class="button button-primary" type="submit">Save changes</button></div>
    </form>
@endsection
