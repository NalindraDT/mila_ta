@extends('layouts.app')

@section('page-title', 'Data Rak')

@section('content')

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Data Rak</h1>
    <a href="{{ route('rak.create') }}" class="btn btn-primary btn-sm shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah Rak
    </a>
</div>

<!-- Alert Success -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Card Tabel -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center">
        <i class="fas fa-th-large mr-2 text-primary"></i>
        <h6 class="m-0 font-weight-bold text-primary">Daftar Rak</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="tableRak" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th width="60px">No</th>
                        <th>Nama Rak</th>
                        <th>Lokasi</th>
                        <th width="160px" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rak as $r)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $r->nama_rak }}</td>
                        <td>{{ $r->lokasi->nama_lokasi ?? '-' }}</td>
                        <td class="text-center" style="white-space: nowrap;">
                            <a href="{{ route('rak.edit', $r->id_rak) }}"
                                class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Belum ada data rak.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#tableRak').DataTable({
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                zeroRecords: "Data tidak ditemukan",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            },
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: [0, -1] }
            ],
            drawCallback: function(settings) {
                const api = this.api();
                api.column(0, { page: 'current' }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1 + api.page.info().start;
                });
            }
        });
    });
</script>
@endsection