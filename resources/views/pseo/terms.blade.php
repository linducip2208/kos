@extends('pseo._layout')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-16">
    <h1 class="text-3xl font-extrabold text-slate-900">Syarat &amp; Ketentuan</h1>
    <p class="text-slate-500 mt-2 text-sm">Terakhir diperbarui: {{ date('d F Y') }}</p>

    <div class="mt-8 space-y-6 text-slate-600 leading-relaxed">
        <section>
            <h2 class="text-lg font-bold text-slate-800">1. Lisensi Penggunaan</h2>
            <p class="mt-2">Pembelian aplikasi {{ setting('app_name', 'Kos Manager') }} memberikan hak pakai sesuai paket yang dipilih (Free, Growth, atau Whitelabel) sebagaimana tercantum di halaman <a href="{{ route('pseo.beli') }}" class="text-primary-700 underline">harga</a>. Dilarang menjual ulang, mendistribusikan, atau mempublikasikan ulang source code kecuali pada paket Whitelabel sesuai ketentuan lisensi yang menyertai pembelian.</p>
        </section>
        <section>
            <h2 class="text-lg font-bold text-slate-800">2. Instalasi &amp; Tanggung Jawab Data</h2>
            <p class="mt-2">Pembeli bertanggung jawab atas keamanan server, kredensial admin, dan pencadangan data. Akun demo bawaan (<code>admin@kos.test</code> dan sejenisnya) wajib dinonaktifkan sebelum go-live dengan perintah <code>php artisan koskosan:remove-demo</code>. Segala kerugian akibat kredensial demo yang dibiarkan aktif bukan tanggung jawab penjual.</p>
        </section>
        <section>
            <h2 class="text-lg font-bold text-slate-800">3. Support &amp; Garansi</h2>
            <p class="mt-2">Support mencakup bantuan instalasi dan perbaikan bug pada fitur bawaan selama masa support paket Anda. Kustomisasi fitur baru, migrasi data dari sistem lain, dan integrasi pihak ketiga di luar daftar resmi dikenakan biaya terpisah.</p>
        </section>
        <section>
            <h2 class="text-lg font-bold text-slate-800">4. Pembayaran &amp; Refund</h2>
            <p class="mt-2">Pembayaran bersifat one-time per paket. Refund diberikan apabila aplikasi tidak dapat diinstal pada environment yang memenuhi persyaratan sistem dan tim kami gagal memperbaikinya dalam 14 hari kerja sejak laporan.</p>
        </section>
        <section>
            <h2 class="text-lg font-bold text-slate-800">5. Perubahan Ketentuan</h2>
            <p class="mt-2">Ketentuan ini dapat diperbarui sewaktu-waktu. Versi yang berlaku adalah yang tertera di halaman ini pada saat pembelian.</p>
        </section>
        <section>
            <h2 class="text-lg font-bold text-slate-800">6. Kontak</h2>
            <p class="mt-2">Pertanyaan seputar lisensi dan pembelian: hubungi kami melalui halaman <a href="{{ route('pseo.beli') }}" class="text-primary-700 underline">pembelian</a> atau WhatsApp yang tercantum di situs ini.</p>
        </section>
    </div>
</div>
@endsection
