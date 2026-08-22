@extends('install.layout', ['step' => 5])

@section('title', 'Finished')
@section('heading', 'Installation complete 🎉')
@section('subheading', 'Your store is ready to use.')

@section('content')
    <div class="alert alert-success">
        The application is installed and your super admin account is ready.
    </div>

    <p class="muted">
        Sign in to the admin panel with the email and password you just created.
        From there you can upload your own logo, configure payment gateways and
        start adding products.
    </p>

    <a class="btn" href="{{ route('admin.login') }}">Go to admin login</a>
@endsection
