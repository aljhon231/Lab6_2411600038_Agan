<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create account · Student Portal</title>
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-intro">
            <a class="brand brand-light" href="{{ route('login') }}"><span class="brand-mark">S</span><span>Student Portal<small>Learning, organized.</small></span></a>
            <div>
                <p class="eyebrow">GET STARTED</p>
                <h1>Your next chapter starts here.</h1>
                <p>Create your student account to browse courses and manage your enrollment.</p>
                <div class="intro-note"><span>01</span> Browse available courses</div>
                <div class="intro-note"><span>02</span> Enroll and follow your history</div>
            </div>
            <p class="intro-foot">A welcoming space for every student.</p>
        </section>
        <section class="auth-form-wrap">
            <form class="auth-card" method="post" action="{{ route('register.store') }}">
                @csrf
                <p class="eyebrow">STUDENT REGISTRATION</p>
                <h2>Create your account</h2>
                <p class="muted">All fields are required unless noted.</p>
                @if($errors->any())
                    <div class="flash flash-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <label for="name">Full name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="120" required autofocus>
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                <button class="button button-primary button-wide" type="submit">Create account <span aria-hidden="true">→</span></button>
                <p class="auth-switch">Already registered? <a href="{{ route('login') }}">Sign in</a></p>
            </form>
        </section>
    </main>
</body>
</html>
