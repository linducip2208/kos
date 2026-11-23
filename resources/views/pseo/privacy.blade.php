@extends('pseo._layout')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-16">
    <h1 class="text-3xl font-extrabold text-slate-900">Kebijakan Privasi</h1>
    <p class="text-slate-500 mt-2 text-sm">Terakhir diperbarui: {{ date('d F Y') }}</p>

    <div class="mt-8 space-y-6 text-slate-600 leading-relaxed">
        <section>
            <h2 class="text-lg font-bold text-slate-800">1. Data yang Kami Kelola</h2>
            <p class="mt-2">Aplikasi ini menyimpan data operasional kos: data properti dan kamar, data penyewa (nama, kontak), kontrak sewa, tagihan dan pembayaran, serta laporan pemeliharaan. Data tersimpan di database milik Anda sendiri (self-hosted) dan tidak dikirim ke server kami kecuali untuk validasi lisensi (pairing).</p>
        </section>
        <section>
            <h2 class="text-lg font-bold text-slate-800">2. Validasi Lisensi</h2>
            <p class="mt-2">Fitur pairing lisensi mengirimkan identifier instalasi dan status langganan ke server lisensi. Tidak ada data penyewa atau data keuangan yang disertakan dalam proses ini.</p>
        </section>
        <section>
            <h2 class="text-lg font-bold text-slate-800">3. Layanan Pihak Ketiga</h2>
            <p class="mt-2">Integrasi opsional seperti payment gateway (Midtrans/Tripay), notifikasi WhatsApp, dan push notification tunduk pada kebijakan privasi masing-masing penyedia. Aktifkan hanya bila Anda menyetujui ketentuan mereka.</p>
        </section>
        <section>
            <h2 class="text-lg font-bold text-slate-800">4. Keamanan</h2>
            <p class="mt-2">Gunakan HTTPS di production, password admin yang kuat (min. 8 karakter), nonaktifkan akun demo, dan jalankan backup terjadwal (<code>backup:run</code> harian). Jangan menjalankan seeder demo di server produksi.</p>
        </section>
        <section>
            <h2 class="text-lg font-bold text-slate-800">5. Hak Pengguna Data (Penyewa)</h2>
            <p class="mt-2">Pengelola kos (pembeli aplikasi) bertindak sebagai pengendali data penyewanya dan wajib memenuhi ketentuan perundang-undangan yang berlaku (termasuk UU Pelindungan Data Pribadi). Aplikasi menyediakan fitur profil dan penghapusan akun sesuai peran.</p>
        </section>
        <section>
            <h2 class="text-lg font-bold text-slate-800">6. Kontak</h2>
            <p class="mt-2">Pertanyaan privasi: hubungi kami melalui halaman <a href="{{ route('pseo.beli') }}" class="text-primary-700 underline">pembelian</a>.</p>
        </section>
    </div>
</div>
@endsection
