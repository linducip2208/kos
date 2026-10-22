<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>Offline — Kos Kosan Pro</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; background: #f6f7f9; color: #111827; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; box-shadow: 0 1px 3px rgba(16,24,40,.06); padding: 2.5rem 2rem; text-align: center; max-width: 26rem; }
        .icon { width: 4rem; height: 4rem; margin: 0 auto 1rem; background: #eff6ff; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; }
        h1 { font-size: 1.35rem; margin-bottom: .5rem; }
        p { color: #6b7280; font-size: .95rem; line-height: 1.6; margin-bottom: 1.5rem; }
        button { background: #2563eb; color: #fff; border: 0; border-radius: 10px; padding: .8rem 1.75rem; font-size: .95rem; font-weight: 600; cursor: pointer; }
        button:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">📡</div>
        <h1>Anda sedang offline</h1>
        <p>Koneksi internet terputus. Periksa jaringan Anda, lalu coba lagi. Data tersimpan tetap aman.</p>
        <button onclick="window.location.reload()">Coba Lagi</button>
    </div>
</body>
</html>
