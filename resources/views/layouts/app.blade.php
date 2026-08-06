<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sistem Informasi Pengelolaan Stok Barang Deck - Kapal Negara Prajapati</title>

    <link href="{{ asset('sb-admin-2/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('sb-admin-2/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet">

    <style>
        body { font-family: 'Nunito', sans-serif; }
        .sidebar .nav-item .nav-link span { font-size: 0.85rem; }
        .sidebar-brand-text { font-size: 1rem !important; font-weight: 800; }
        .sidebar-brand-icon img { width: 32px; height: 32px; }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">

        <!-- SIDEBAR -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/') }}">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-ship fa-lg"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Deck Prajapati</div>
            </a>

            <hr class="sidebar-divider my-0">

            <!-- Dashboard (Semua Role) -->
            <li class="nav-item {{ request()->is('/') || request()->is('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">Master Data</div>

            @php
                $isBarangActive = request()->is('satuan*') || request()->is('lokasi*') || request()->is('rak*') || request()->is('barang') || request()->is('barang/create') || request()->is('barang/*/edit');
            @endphp

            <!-- Menu Barang -->
            <li class="nav-item {{ $isBarangActive ? 'active' : '' }}">
                <a class="nav-link {{ $isBarangActive ? '' : 'collapsed' }}"
                    href="#"
                    data-toggle="collapse"
                    data-target="#collapseBarang"
                    aria-expanded="{{ $isBarangActive ? 'true' : 'false' }}"
                    aria-controls="collapseBarang">
                    <i class="fas fa-fw fa-boxes"></i>
                    <span>Barang</span>
                </a>
                <div id="collapseBarang"
                    class="collapse {{ $isBarangActive ? 'show' : '' }}"
                    aria-labelledby="headingBarang"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Kelola:</h6>

                        <!-- Master Data: Hanya untuk Kepala Gudang -->
                        @if(auth()->user()->role == 'Kepala Gudang')
                        <a class="collapse-item {{ request()->is('satuan*') ? 'active' : '' }}" href="{{ route('satuan.index') }}">
                            <i class="fas fa-ruler-combined mr-1"></i> Satuan
                        </a>
                        <a class="collapse-item {{ request()->is('lokasi*') ? 'active' : '' }}" href="{{ route('lokasi.index') }}">
                            <i class="fas fa-map-marker-alt mr-1"></i> Lokasi
                        </a>
                        <a class="collapse-item {{ request()->is('rak*') ? 'active' : '' }}" href="{{ route('rak.index') }}">
                            <i class="fas fa-th-large mr-1"></i> Rak
                        </a>
                        @endif

                        <!-- Data Barang: Bisa dilihat semua role -->
                        <a class="collapse-item {{ request()->is('barang') || request()->is('barang/create') || request()->is('barang/*/edit') ? 'active' : '' }}" href="{{ route('barang.index') }}">
                            <i class="fas fa-barcode mr-1"></i> Data Barang
                        </a>
                    </div>
                </div>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">Transaksi</div>

            <!-- Barang Masuk (Semua role: Staff untuk input, Admin/Kepala untuk ACC) -->
            <li class="nav-item {{ request()->is('barang-masuk*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('barang-masuk.index') }}">
                    <i class="fas fa-fw fa-arrow-circle-down"></i>
                    <span>Barang Masuk</span>
                </a>
            </li>

            <!-- Barang Keluar (Semua role) -->
            <li class="nav-item {{ request()->is('barang-keluar*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('barang-keluar.index') }}">
                    <i class="fas fa-fw fa-arrow-circle-up"></i>
                    <span>Barang Keluar</span>
                </a>
            </li>

            <!-- Laporan: Hanya untuk Admin Gudang & Kepala Gudang -->
            @if(in_array(auth()->user()->role, ['Admin Gudang', 'Kepala Gudang']))
            <hr class="sidebar-divider">

            <div class="sidebar-heading">Laporan</div>

            @php
                $isLaporanStokActive   = request()->is('laporan');
                $isLaporanMasukActive  = request()->is('laporan-barang-masuk*');
                $isLaporanKeluarActive = request()->is('laporan-barang-keluar*');
                $isLaporanActive       = $isLaporanStokActive || $isLaporanMasukActive || $isLaporanKeluarActive;
            @endphp

            <!-- Menu Laporan -->
            <li class="nav-item {{ $isLaporanActive ? 'active' : '' }}">
                <a class="nav-link {{ $isLaporanActive ? '' : 'collapsed' }}"
                    href="#"
                    data-toggle="collapse"
                    data-target="#collapseLaporan"
                    aria-expanded="{{ $isLaporanActive ? 'true' : 'false' }}"
                    aria-controls="collapseLaporan">
                    <i class="fas fa-fw fa-file-alt"></i>
                    <span>Laporan</span>
                </a>
                <div id="collapseLaporan"
                    class="collapse {{ $isLaporanActive ? 'show' : '' }}"
                    aria-labelledby="headingLaporan"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Pilih Laporan:</h6>
                        <a class="collapse-item {{ $isLaporanStokActive ? 'active' : '' }}" href="{{ route('laporan.index') }}">
                            <i class="fas fa-boxes mr-1"></i> Laporan Stok Barang
                        </a>
                        <a class="collapse-item {{ $isLaporanMasukActive ? 'active' : '' }}" href="{{ route('laporan-barang-masuk.index') }}">
                            <i class="fas fa-arrow-circle-down mr-1"></i> Laporan Barang Masuk
                        </a>
                        <a class="collapse-item {{ $isLaporanKeluarActive ? 'active' : '' }}" href="{{ route('laporan-barang-keluar.index') }}">
                            <i class="fas fa-arrow-circle-up mr-1"></i> Laporan Barang Keluar
                        </a>
                    </div>
                </div>
            </li>
            @endif

            <!-- Pengaturan Sistem: Hanya untuk Kepala Gudang -->
            @if(auth()->user()->role == 'Kepala Gudang')
            <hr class="sidebar-divider">
            
            <div class="sidebar-heading">Pengaturan Sistem</div>

            <!-- Manajemen User -->
            <li class="nav-item {{ request()->is('manajemen-user*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('manajemen-user.index') }}">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Manajemen User</span>
                </a>
            </li>

            <!-- Log Aktivitas -->
            <li class="nav-item {{ request()->is('log-aktivitas*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('log-aktivitas.index') }}">
                    <i class="fas fa-fw fa-history"></i>
                    <span>Log Aktivitas</span>
                </a>
            </li>
            @endif

            <hr class="sidebar-divider d-none d-md-block">

            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- END SIDEBAR -->

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">

                <!-- TOPBAR -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <span class="font-weight-bold text-primary">
                        @yield('page-title', 'Dashboard')
                    </span>

                    <ul class="navbar-nav ml-auto">
                        <div class="topbar-divider d-none d-sm-block"></div>

                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    {{ auth()->user()->nama_user }} ({{ auth()->user()->role }})
                                </span>
                                <i class="fas fa-user-circle fa-lg text-primary"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="{{ route('ubah-password') }}">
                                    <i class="fas fa-key fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Ubah Password
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- END TOPBAR -->

                <div class="container-fluid">
                    @yield('content')
                </div>

            </div>

            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Sistem Informasi Pengelolaan Stok Barang Deck &copy; {{ date('Y') }}</span>
                    </div>
                </div>
            </footer>

        </div>

    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yakin mau logout?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Klik "Logout" untuk keluar dari sistem.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('sb-admin-2/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('sb-admin-2/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('sb-admin-2/js/sb-admin-2.min.js') }}"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    @yield('scripts')

</body>

</html>