<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontrak Ditandatangani</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-2xl shadow p-10 max-w-md text-center">
    <div class="text-5xl">✅</div>
    <h1 class="text-xl font-extrabold mt-3">Kontrak {{ $contract->contract_number }} berhasil ditandatangani</h1>
    <p class="text-sm text-slate-500 mt-2">Status: {{ $contract->status_label }} · {{ $contract->occupant_signed_at?->format('d M Y H:i') }}</p>
    <a href="/" class="inline-block mt-6 bg-blue-600 text-white px-6 py-3 rounded-xl font-bold">Kembali ke Beranda</a>
</div>
</body>
</html>
