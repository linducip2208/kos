@extends('portal.layouts.app')
@section('title', 'Maintenance')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="font-semibold text-gray-700">Laporan Kerusakan</h2>
    <a href="{{ route('portal.maintenance.create') }}"
        class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-blue-700">
        + Lapor Baru
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @forelse($requests as $req)
    <div class="p-4 border-b last:border-0">
        <div class="flex justify-between items-start">
            <div>
                <div class="font-medium text-sm">{{ $req->title }}</div>
                <div class="text-xs text-gray-500 mt-0.5">
                    Kamar: {{ $req->room?->room_number ?? '-' }} •
                    {{ $req->created_at->format('d M Y') }}
                </div>
                <div class="text-xs text-gray-600 mt-1">{{ Str::limit($req->description, 80) }}</div>
            </div>
            <div class="ml-3 shrink-0">
                <span class="text-xs px-2 py-0.5 rounded font-medium
                    {{ match($req->status) {
                        'open' => 'bg-red-100 text-red-700',
                        'in_progress' => 'bg-yellow-100 text-yellow-700',
                        'resolved' => 'bg-green-100 text-green-700',
                        default => 'bg-gray-100 text-gray-600'
                    } }}">
                    {{ match($req->status) {
                        'open' => 'Menunggu',
                        'in_progress' => 'Diproses',
                        'resolved' => 'Selesai',
                        default => 'Dibatalkan'
                    } }}
                </span>
            </div>
        </div>
    </div>
    @empty
    <div class="p-8 text-center text-gray-400">
        <div class="text-4xl mb-2">🔧</div>
        Belum ada laporan kerusakan.
    </div>
    @endforelse

    <div class="p-4">{{ $requests->links() }}</div>
</div>
@endsection
