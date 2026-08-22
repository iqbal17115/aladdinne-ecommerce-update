@extends('install.layout', ['step' => 2])

@section('title', 'Database')
@section('heading', 'Database & application URL')
@section('subheading', 'The connection is tested before anything is saved to your .env file.')

@section('content')
    <form method="POST" action="{{ route('install.database.save') }}">
        @csrf

        <label for="app_url">Application URL</label>
        <input type="url" id="app_url" name="app_url" required
               value="{{ old('app_url', url('/')) }}" placeholder="https://example.com">

        <div class="row">
            <div>
                <label for="db_host">Database host</label>
                <input type="text" id="db_host" name="db_host" required value="{{ old('db_host', '127.0.0.1') }}">
            </div>
            <div>
                <label for="db_port">Port</label>
                <input type="number" id="db_port" name="db_port" required value="{{ old('db_port', 3306) }}">
            </div>
        </div>

        <label for="db_database">Database name</label>
        <input type="text" id="db_database" name="db_database" required value="{{ old('db_database') }}">

        <label for="db_username">Database username</label>
        <input type="text" id="db_username" name="db_username" required value="{{ old('db_username') }}">

        <label for="db_password">Database password</label>
        <input type="password" id="db_password" name="db_password" value="{{ old('db_password') }}">

        <button type="submit" class="btn">Test connection & save</button>
    </form>
@endsection
