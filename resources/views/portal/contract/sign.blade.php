<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanda Tangan Kontrak — {{ $contract->contract_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen py-8">
<div class="max-w-3xl mx-auto px-4">
    <div class="bg-white rounded-2xl shadow p-6 md:p-10">
        <h1 class="text-2xl font-extrabold">Kontrak Digital {{ $contract->contract_number }}</h1>
        <p class="text-sm text-slate-500 mt-1">Penyewa: {{ $contract->lease?->occupant?->name ?? '-' }} · Kamar: {{ $contract->lease?->room?->room_number ?? '-' }}</p>
        <div class="prose max-w-none mt-6 border rounded-xl p-4 bg-slate-50 text-sm">{!! $contract->content_html !!}</div>

        <form method="POST" action="{{ route('portal.contract.sign.post', $contract->sign_token) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
            @csrf
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 text-sm">
                    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                </div>
            @endif
            <div>
                <label class="font-semibold text-sm">Upload tanda tangan (PNG/JPG, maks 2MB)</label>
                <input type="file" name="signature" accept="image/png,image/jpeg" required class="mt-1 block w-full text-sm border rounded-lg p-2">
            </div>
            <label class="flex items-start gap-2 text-sm">
                <input type="checkbox" name="agree" value="1" required class="mt-1">
                <span>Saya menyetujui seluruh isi kontrak di atas dan menandatanganinya secara digital.</span>
            </label>
            <button class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold w-full">Tanda Tangani Kontrak</button>
        </form>
    </div>
</div>
</body>
</html>
