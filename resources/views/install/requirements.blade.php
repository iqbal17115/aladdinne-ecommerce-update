@extends('install.layout', ['step' => 1])

@section('title', 'Requirements')
@section('heading', 'Server requirements')
@section('subheading', 'Everything below must pass before installation can begin.')

@section('content')
    <table>
        @foreach ($checks as $check)
            <tr>
                <td>{{ $check['label'] }}</td>
                <td style="text-align:right">
                    <span class="badge {{ $check['passed'] ? 'pass' : 'fail' }}">
                        {{ $check['passed'] ? 'Pass' : 'Fail' }}
                    </span>
                </td>
            </tr>
        @endforeach
    </table>

    @if (! $allPassed)
        <button class="btn" disabled>Fix the failures above, then reload</button>
    @elseif ($tokenVerified)
        <a class="btn" href="{{ route('install.database') }}">Continue</a>
    @else
        <hr style="margin:22px 0;border:none;border-top:1px solid #e2e8f0">
        <div class="alert alert-warning" style="margin-bottom:14px">
            <strong>Security check.</strong> To prove you own this server, open the file
            below (via cPanel File Manager, FTP or SSH), copy the token inside it, and
            paste it here. This stops anyone else from completing the installation.
            <div class="muted" style="margin-top:8px;word-break:break-all">
                <code>{{ $tokenPath }}</code>
            </div>
        </div>

        <form method="POST" action="{{ route('install.verify-token') }}">
            @csrf
            <label for="install_token">Install token</label>
            <input type="text" id="install_token" name="install_token" required
                   autocomplete="off" placeholder="Paste the token from the file above">
            <button type="submit" class="btn">Verify & continue</button>
        </form>
    @endif
@endsection
