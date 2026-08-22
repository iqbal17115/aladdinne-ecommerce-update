@extends('install.layout', ['step' => 4])

@section('title', 'Admin account')
@section('heading', 'Create the super admin')
@section('subheading', 'This account gets full access to the admin panel.')

@section('content')
    @if (session('seed_warnings') && count(session('seed_warnings')))
        <div class="alert alert-warning">
            <strong>Some optional seeders were skipped:</strong>
            @foreach (session('seed_warnings') as $warning)
                <div>{{ $warning }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('install.admin.save') }}">
        @csrf

        <label for="name">Name</label>
        <input type="text" id="name" name="name" required value="{{ old('name') }}">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="{{ old('email') }}">

        <label for="password">Password (min 8 characters)</label>
        <input type="password" id="password" name="password" required minlength="8">

        <label for="password_confirmation">Confirm password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">

        <button type="submit" class="btn">Create admin & finish</button>
    </form>
@endsection
