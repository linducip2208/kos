<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Portal Penyewa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">

<div class="bg-white rounded-2xl shadow-lg w-full max-w-sm p-8">
    <div class="text-center mb-8">
        <div class="text-4xl mb-2">🏠</div>
        <h1 class="text-2xl font-bold text-gray-800">Portal Penyewa</h1>
        <p class="text-gray-500 text-sm mt-1">{{ setting('app_name', 'Kos Manager') }}</p>
    </div>

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('portal.login.post') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
            <input type="text" name="phone" value="{{ old('phone') }}" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="08xxxxxxxxxx">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="password" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="••••••••">
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="remember" id="remember" class="rounded">
            <label for="remember" class="text-sm text-gray-600">Ingat saya</label>
        </div>
        <button type="submit"
            class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium hover:bg-blue-700 transition">
            Masuk
        </button>
    </form>

    <p class="text-center text-xs text-gray-400 mt-6">
        Belum punya akses? Hubungi pengelola kos.
    </p>
</div>

</body>
</html>
