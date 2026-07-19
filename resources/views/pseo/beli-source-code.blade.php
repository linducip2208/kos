@extends('pseo._layout')

@section('content')
<div class="bg-gradient-to-br from-slate-800 via-slate-900 to-indigo-950 py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white">Beli Aplikasi Kos Manager</h1>
        <p class="text-slate-300 mt-6 text-lg max-w-2xl mx-auto">Source code aplikasi manajemen kos siap pakai. Fitur lengkap, mudah dikustomisasi, one-time payment.</p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-16">
    <div class="grid md:grid-cols-3 gap-8">
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200 text-center">
            <h3 class="text-xl font-bold text-slate-800">Free</h3>
            <p class="text-3xl font-extrabold text-slate-900 mt-4">Rp 0</p>
            <p class="text-slate-500 mt-2">Selamanya</p>
            <ul class="mt-6 space-y-3 text-left text-sm text-slate-600">
                <li>✓ 1 Properti</li>
                <li>✓ 10 Kamar</li>
                <li>✓ Invoice Manual</li>
                <li>✓ Landing Page</li>
                <li>✗ Payment Gateway</li>
                <li>✗ Portal Penghuni</li>
            </ul>
            <a href="/admin/register" class="block mt-8 bg-slate-100 text-slate-700 py-3 rounded-xl font-semibold hover:bg-slate-200 transition-colors">Coba Gratis</a>
        </div>

        <div class="bg-gradient-to-br from-primary-600 to-indigo-700 rounded-2xl p-8 shadow-xl border-2 border-primary-500 text-center text-white transform scale-105">
            <span class="bg-white/20 text-white text-xs px-3 py-1 rounded-full font-medium">POPULER</span>
            <h3 class="text-xl font-bold mt-3">Growth</h3>
            <p class="text-4xl font-extrabold mt-4">Rp 2.5jt</p>
            <p class="text-primary-100 mt-2">One-time</p>
            <ul class="mt-6 space-y-3 text-left text-sm">
                <li>✓ Unlimited Properti</li>
                <li>✓ Unlimited Kamar</li>
                <li>✓ Invoice Otomatis</li>
                <li>✓ Payment Gateway</li>
                <li>✓ Portal Penghuni</li>
                <li>✓ Laporan Keuangan</li>
                <li>✓ E-Contract</li>
                <li>✓ Maintenance System</li>
            </ul>
            <a href="https://wa.me/6281296052010" class="block mt-8 bg-white text-primary-700 py-3 rounded-xl font-bold hover:bg-blue-50 transition-colors">Beli Sekarang</a>
        </div>

        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200 text-center">
            <h3 class="text-xl font-bold text-slate-800">Whitelabel</h3>
            <p class="text-3xl font-extrabold text-slate-900 mt-4">Rp 10jt</p>
            <p class="text-slate-500 mt-2">One-time</p>
            <ul class="mt-6 space-y-3 text-left text-sm text-slate-600">
                <li>✓ Semua fitur Growth</li>
                <li>✓ Source Code Full</li>
                <li>✓ Rebranding Bebas</li>
                <li>✓ Multi-Tenant</li>
                <li>✓ Plugin System</li>
                <li>✓ Theme Engine</li>
                <li>✓ API Mobile Ready</li>
                <li>✓ Prioritas Support</li>
            </ul>
            <a href="https://wa.me/6281296052010" class="block mt-8 bg-slate-800 text-white py-3 rounded-xl font-semibold hover:bg-slate-900 transition-colors">Kontak Sales</a>
        </div>
    </div>
</div>
@endsection
