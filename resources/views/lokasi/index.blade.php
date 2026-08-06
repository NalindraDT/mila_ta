@extends('layouts.app')

@section('page-title', 'Data Lokasi')

@section('content')

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Data Lokasi</h1>
    <a href="{{ route('lokasi.create') }}" class="btn btn-primary btn-sm shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah Lokasi
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
        <i class="fas fa-map-marker-alt mr-2 text-primary"></i>
        <h6 class="m-0 font-weight-bold text-primary">Daftar Lokasi</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="tableLokasi" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th width="60px">No</th>
                        <th>Nama Lokasi</th>
                        <th width="160px" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lokasi as $l)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $l->nama_lokasi }}</td>
                        <td class="text-center" style="white-space: nowrap;">
                            <a href="{{ route('lokasi.edit', $l->id_lokasi) }}"
                                class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">Belum ada data lokasi.</td>
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
        $('#tableLokasi').DataTable({
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