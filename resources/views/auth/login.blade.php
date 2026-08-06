<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - Deck Prajapati</title>

    <link href="{{ asset('sb-admin-2/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('sb-admin-2/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="bg-gradient-primary">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-5 col-lg-6 col-md-8">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="p-5">

                        <!-- Logo & Judul -->
                        <div class="text-center mb-4">
                            <i class="fas fa-ship fa-3x text-primary mb-3"></i>
                            <h4 class="font-weight-bold text-primary">Deck Prajapati</h4>
                            <p class="text-muted small">Sistem Informasi Pengelolaan Stok Barang</p>
                            <p class="text-gray-800 font-weight-bold mt-2">Selamat Datang di Sistem Informasi Pengelolaan Stok Barang Bagian Deck!</p>
                        </div>

                        <!-- Alert error login -->
                        @if($errors->has('login'))
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $errors->first('login') }}
                            </div>
                        @endif

                        <!-- Form Login -->
                        <form action="{{ route('login.post') }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text"
                                    class="form-control @error('username') is-invalid @enderror"
                                    id="username"
                                    name="username"
                                    value="{{ old('username') }}"
                                    placeholder="Masukkan username">
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <div class="input-group">
                                    <input type="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        id="password"
                                        name="password"
                                        placeholder="Masukkan password">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye" id="eyeIcon"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block mt-4">
                                <i class="fas fa-sign-in-alt mr-1"></i> Login
                            </button>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('sb-admin-2/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('sb-admin-2/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('sb-admin-2/js/sb-admin-2.min.js') }}"></script>

<script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const password = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (password.type === 'password') {
            password.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            password.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    });
</script>

</body>
</html>