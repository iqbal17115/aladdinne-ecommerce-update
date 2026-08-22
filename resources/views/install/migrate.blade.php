@extends('install.layout', ['step' => 3])

@section('title', 'Migrate')
@section('heading', 'Database setup')
@section('subheading', 'Creates all tables and seeds the required base data.')

@section('content')
    <p class="muted">
        This runs all migrations and the essential seeders (settings, pages, currencies,
        roles &amp; permissions). It can take a minute — please don't close the page.
    </p>

    <form method="POST" action="{{ route('install.migrate.run') }}" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = 'Running…';">
        @csrf

        <div class="check">
            <input type="checkbox" id="demo_data" name="demo_data" value="1">
            <label for="demo_data" style="margin:0">Also install demo data (sample shops, products, orders)</label>
        </div>

        <button type="submit" class="btn">Run migrations</button>
    </form>
@endsection
