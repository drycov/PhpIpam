@extends('layouts.app')

@section('title', 'phpIPAM Plugin Settings')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">⚙️ phpIPAM Plugin Settings</h2>

    {{-- Сообщение о статусе --}}
    @if(session('status'))
        <div class="alert alert-info">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('plugin.save_config', ['plugin' => 'PhpIpam']) }}" class="needs-validation" novalidate>
        @csrf

        <div class="mb-3">
            <label class="form-label fw-bold">phpIPAM API URL</label>
            <input type="url" name="url" value="{{ $config['url'] ?? '' }}" class="form-control" required>
            <div class="form-text text-muted">Example: https://ipam.company.local/api</div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">App ID</label>
            <input type="text" name="app_id" value="{{ $config['app_id'] ?? '' }}" class="form-control" required>
            <div class="form-text">Application identifier configured in phpIPAM API section.</div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Username</label>
                <input type="text" name="username" value="{{ $config['username'] ?? '' }}" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Password</label>
                <input type="password" name="password" value="{{ $config['password'] ?? '' }}" class="form-control">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">API Token</label>
            <input type="text" name="token" value="{{ $config['token'] ?? '' }}" class="form-control" required>
            <div class="form-text text-muted">Bearer token generated in phpIPAM for API access.</div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Verify SSL</label>
                <select name="verify_ssl" class="form-select">
                    <option value="1" {{ ($config['verify_ssl'] ?? true) ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !($config['verify_ssl'] ?? true) ? 'selected' : '' }}>No</option>
                </select>
                <div class="form-text text-muted">Disable only for testing or self-signed certificates.</div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Sync Interval (seconds)</label>
                <input type="number" name="sync_interval" value="{{ $config['sync_interval'] ?? 3600 }}" class="form-control" min="60">
                <div class="form-text">Default: 3600 seconds (1 hour)</div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                💾 Save Settings
            </button>

            <a href="{{ url('phpipam-plugin/test') }}" class="btn btn-outline-success">
                🧪 Test Connection
            </a>

            <a href="{{ url('phpipam-plugin') }}" class="btn btn-outline-secondary">
                ← Back to Records
            </a>
        </div>
    </form>
</div>
@endsection
