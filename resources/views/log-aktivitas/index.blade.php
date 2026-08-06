@extends('layouts.app')

@section('page-title', 'Log Aktivitas')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Log Aktivitas User</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center">
        <i class="fas fa-history mr-2 text-primary"></i>
        <h6 class="m-0 font-weight-bold text-primary">Riwayat Aktivitas Sistem</h6>
    </div>
    <div class="card-body">
        
        <!-- Form Filter Tanggal -->
        <form action="{{ route('log-aktivitas.index') }}" method="GET" class="mb-4">
            <div class="form-row align-items-end">
                <div class="col-md-3 col-sm-12 mb-3">
                    <label for="start_date" class="font-weight-bold">Dari Tanggal</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                        value="{{ request('start_date') }}" required>
                </div>
                <div class="col-md-3 col-sm-12 mb-3">
                    <label for="end_date" class="font-weight-bold">Sampai Tanggal</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                        value="{{ request('end_date') }}" required>
                </div>
                <div class="col-md-4 col-sm-12 mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('log-aktivitas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-sync-alt mr-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
        <hr>

        <!-- Tabel Data -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th width="50px" class="text-center">No</th>
                        <th width="180px">Waktu</th>
                        <th width="200px">Pengguna (Role)</th>
                        <th>Aktivitas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d-m-Y H:i:s') }}</td>
                        <td>
                            <strong>{{ $log->user->nama_user ?? 'User Telah Dihapus' }}</strong><br>
                            <small class="text-muted">{{ $log->user->role ?? '-' }}</small>
                        </td>
                        <td>{{ $log->aktivitas }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "order": [], 
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" 
            }
        });
    });
</script>
@endsection