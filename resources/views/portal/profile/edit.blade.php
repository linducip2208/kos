@extends('portal.layouts.app')
@section('title', 'Profil')

@section('content')
<div class="grid gap-4 max-w-lg mx-auto">

    {{-- Profile Update --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-700 mb-5">Data Profil</h2>
        <form method="POST" action="{{ route('portal.profile.update') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $occupant->name) }}" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
                <input type="text" name="phone" value="{{ old('phone', $occupant->phone) }}" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $occupant->email) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                <input type="text" name="whatsapp" value="{{ old('whatsapp', $occupant->whatsapp) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium hover:bg-blue-700">
                Simpan Perubahan
            </button>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-700 mb-5">Ubah Password</h2>
        <form method="POST" action="{{ route('portal.profile.password') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
                <input type="password" name="current_password" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                <input type="password" name="password" required minlength="6"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-green-600 text-white py-2.5 rounded-lg font-medium hover:bg-green-700">
                Ubah Password
            </button>
        </form>
    </div>

</div>
@endsection
