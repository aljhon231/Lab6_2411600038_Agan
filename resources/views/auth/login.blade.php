<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · Student Portal</title>
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-intro">
            <a class="brand brand-light" href="{{ route('login') }}"><span class="brand-mark">S</span><span>Student Portal<small>Learning, organized.</small></span></a>
            <div>
                <p class="eyebrow">YOUR ACADEMIC HOME</p>
                <h1>Make room for what’s next.</h1>
                <p>Explore your courses, keep track of your enrollment, and stay up to date with your studies.</p>
                <div class="intro-note"><span>01</span> Course availability that stays current</div>
                <div class="intro-note"><span>02</span> A clear record of your enrollment activity</div>
            </div>
            <p class="intro-foot">A welcoming space for every student.</p>
        </section>
        <section class="auth-form-wrap">
            <form class="auth-card" method="post" action="{{ route('login.store') }}">
                @csrf
                <p class="eyebrow">WELCOME BACK</p>
                <h2>Sign in to your account</h2>
                <p class="muted">Use the email address you registered with.</p>
                @if($errors->any())
                    <div class="flash flash-error" role="alert">{{ $errors->first() }}</div>
                @endif
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                <label class="check-row"><input type="checkbox" name="remember" value="1"> Remember me</label>
                <button class="button button-primary button-wide" type="submit">Sign in <span aria-hidden="true">→</span></button>
                <p class="auth-switch">New to the portal? <a href="{{ route('register') }}">Create an account</a></p>
            </form>
        </section>
    </main>
</body>
</html>
