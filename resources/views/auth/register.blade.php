<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - SmartRide AI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a, #14532d);
            display: grid;
            place-items: center;
            color: #0f172a;
        }

        .auth-card {
            width: 92%;
            max-width: 430px;
            background: white;
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 20px 45px rgba(0,0,0,0.18);
        }

        .logo {
            width: 58px;
            height: 58px;
            background: linear-gradient(135deg, #16a34a, #2563eb);
            color: white;
            border-radius: 18px;
            display: grid;
            place-items: center;
            font-weight: 900;
            margin-bottom: 16px;
        }

        h1 {
            margin: 0;
            font-size: 28px;
        }

        p {
            color: #64748b;
            line-height: 1.6;
        }

        label {
            display: block;
            margin-top: 16px;
            margin-bottom: 7px;
            font-weight: 800;
        }

        input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 13px;
            background: #f8fafc;
        }

        button {
            margin-top: 22px;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, #16a34a, #2563eb);
            color: white;
            font-weight: 900;
            cursor: pointer;
        }

        .error-box {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 12px;
            margin-top: 16px;
        }

        .auth-link {
            margin-top: 18px;
            text-align: center;
        }

        .auth-link a {
            color: #16a34a;
            font-weight: 800;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="logo">SR</div>
    <h1>Daftar Akun</h1>
    <p>Buat akun untuk menggunakan SmartRide AI dan menyimpan riwayat pemesanan.</p>

    @if ($errors->any())
        <div class="error-box">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('register.process') }}" method="POST">
        @csrf

        <label>Nama</label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Masukkan nama">

        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email">

        <label>Password</label>
        <input type="password" name="password" placeholder="Minimal 6 karakter">

        <label>Konfirmasi Password</label>
        <input type="password" name="password_confirmation" placeholder="Ulangi password">

        <button type="submit">Daftar</button>
    </form>

    <div class="auth-link">
        Sudah punya akun? <a href="{{ route('login') }}">Login</a>
    </div>
</div>

</body>
</html>