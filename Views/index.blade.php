@extends('layouts.app')

@section('title', 'phpIPAM Integration')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">📡 phpIPAM Sync Records</h2>

    {{-- Статус сообщения --}}
    @if(session('status'))
        <div class="alert alert-info">
            {{ session('status') }}
        </div>
    @endif

    {{-- Вывод лога Artisan --}}
    @if(session('output'))
        <div class="alert alert-secondary" style="white-space: pre-wrap; font-family: monospace;">
            {{ session('output') }}
        </div>
    @endif

    {{-- Информация о последнем обновлении --}}
    @if(isset($last_sync))
        <p class="text-muted">
            <strong>Last sync:</strong> {{ $last_sync }}
            @if($phpipam_url)
                | <strong>phpIPAM URL:</strong> <a href="{{ $phpipam_url }}" target="_blank">{{ $phpipam_url }}</a>
            @endif
        </p>
    @endif

    {{-- Таблица данных --}}
    <div class="table-responsive">
        <table class="table table-striped table-sm align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Device</th>
                    <th>IP</th>
                    <th>Subnet</th>
                    <th>Status</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                    <tr>
                        <td>{{ $r->id }}</td>
                        <td>{{ $r->device_id ?? '-' }}</td>
                        <td>{{ $r->ip_address ?? '-' }}</td>
                        <td>{{ $r->subnet ?? '-' }}</td>
                        <td>
                            @php
                                $status = strtolower($r->status ?? 'unknown');
                                $badge = match($status) {
                                    'active' => 'success',
                                    'reserved' => 'warning',
                                    'offline', 'inactive' => 'danger',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ ucfirst($status) }}</span>
                        </td>
                        <td>{{ $r->updated_at ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No records found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Пагинация --}}
    <div class="mt-3">
        {{ $records->links() }}
    </div>

    {{-- Кнопки управления --}}
    <div class="d-flex gap-2 mt-4">
        <form method="POST" action="{{ url('phpipam-plugin/sync') }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                🔄 Запустить Sync
            </button>
        </form>

        <form method="GET" action="{{ url('phpipam-plugin/test') }}">
            <button type="submit" class="btn btn-outline-success">
                🧪 Test Connection
            </button>
        </form>
    </div>
</div>
@endsection
