@extends('portal.layouts.app')
@section('title', 'Lapor Kerusakan')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-lg mx-auto">
    <h2 class="font-semibold text-gray-700 mb-5">Lapor Kerusakan / Masalah</h2>

    <form method="POST" action="{{ route('portal.maintenance.store') }}" class="space-y-4">
        @csrf

        @if($lease)
        <div class="bg-blue-50 rounded-lg p-3 text-sm text-blue-700">
            Kamar: {{ $lease->room->property->name ?? '' }} — {{ $lease->room->room_number ?? '' }}
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Laporan <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="cth: AC tidak dingin, kran bocor, dll">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi <span class="text-red-500">*</span></label>
            <textarea name="description" required rows="4"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Jelaskan masalah secara detail...">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
            <select name="priority" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="low">Rendah — tidak urgent</option>
                <option value="medium" selected>Sedang — perlu segera</option>
                <option value="high">Tinggi — mengganggu kenyamanan</option>
                <option value="urgent">Urgent — darurat!</option>
            </select>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="flex-1 bg-blue-600 text-white py-2.5 rounded-lg font-medium hover:bg-blue-700">
                Kirim Laporan
            </button>
            <a href="{{ route('portal.maintenance.index') }}"
                class="flex-1 text-center border border-gray-300 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
