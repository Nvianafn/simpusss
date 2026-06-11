<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login SIMPUS</title>
    <style>
        :root { font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color-scheme: light; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: linear-gradient(135deg, #0f172a, #1d4ed8); color: #0f172a; padding: 20px; }
        .card { width: min(440px, 100%); background: white; border-radius: 28px; padding: 30px; box-shadow: 0 30px 80px rgba(15,23,42,.35); }
        h1 { margin: 0 0 8px; font-size: 34px; letter-spacing: -.04em; }
        p { margin: 0 0 22px; color: #64748b; line-height: 1.6; }
        label { display: block; font-weight: 800; margin: 14px 0 8px; }
        input { width: 100%; border: 1px solid #cbd5e1; border-radius: 16px; padding: 13px 14px; font-size: 15px; }
        button, a.btn { width: 100%; display: inline-flex; justify-content: center; border: 0; border-radius: 999px; padding: 13px 16px; font-weight: 900; margin-top: 18px; background: #facc15; color: #111827; cursor: pointer; text-decoration: none; }
        a.btn { background: #e2e8f0; margin-top: 10px; }
        .error { background: #fee2e2; color: #991b1b; border-radius: 16px; padding: 12px 14px; margin-bottom: 14px; }
        .hint { background: #eff6ff; color: #1e40af; border-radius: 16px; padding: 12px 14px; margin-top: 16px; font-size: 14px; }
    </style>
</head>
<body>
    <main class="card">
        <h1>Login SIMPUS</h1>
        <p>Frontend login ke Auth Service, token disimpan di session server Laravel, bukan di JavaScript browser.</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', 'admin@simpus.test') }}" required autofocus>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" value="password123" required>

            <button type="submit">Masuk ke Portal</button>
        </form>

        <a class="btn" href="{{ route('dashboard') }}">Lihat Dashboard Read-only</a>
        <div class="hint">Akun demo dari seeder: admin@simpus.test. Password hanya untuk environment lokal tugas kuliah.</div>
    </main>
</body>
</html>
