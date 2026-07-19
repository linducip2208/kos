<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\Portal\AuthController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\InvoiceController;
use App\Http\Controllers\Portal\MaintenanceController;
use App\Http\Controllers\Portal\ProfileController;
use App\Http\Controllers\PrintController;
use Illuminate\Support\Facades\Route;

// Landing Page
Route::get('/', [LandingController::class, 'home'])->name('landing.home');
Route::get('/property/{property}', [LandingController::class, 'property'])->name('landing.property');
Route::post('/contact', [LandingController::class, 'contact'])->name('landing.contact');

// Webhook payment gateway — CSRF dikecualikan karena request dari server eksternal
Route::post('/webhook/midtrans', [PaymentWebhookController::class, 'midtrans'])
    ->name('webhook.midtrans')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);

Route::post('/webhook/tripay', [PaymentWebhookController::class, 'tripay'])
    ->name('webhook.tripay')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);

// Online Booking
Route::get('/booking/{property}',  [BookingController::class, 'show'])->name('booking.show');
Route::post('/booking/{property}', [BookingController::class, 'store'])->name('booking.store');

// Cetak / Print PDF & Excel (admin via session, penyewa via portal guard)
Route::prefix('print')->name('print.')->group(function () {
    Route::get('invoice/{invoice}',          [PrintController::class, 'invoice'])->name('invoice');
    Route::get('kwitansi/{invoice}',         [PrintController::class, 'kwitansi'])->name('kwitansi');
    Route::get('report/invoices',            [PrintController::class, 'reportInvoices'])->name('report.invoices');
    Route::get('excel/invoices',             [PrintController::class, 'excelInvoices'])->name('excel.invoices');
    Route::get('excel/occupancy',            [PrintController::class, 'excelOccupancy'])->name('excel.occupancy');
});

// Portal Penyewa
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');

    Route::middleware('portal.auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/',          [DashboardController::class, 'index'])->name('dashboard');
        Route::get('invoices',   [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');

        Route::get('maintenance',         [MaintenanceController::class, 'index'])->name('maintenance.index');
        Route::get('maintenance/create',  [MaintenanceController::class, 'create'])->name('maintenance.create');
        Route::post('maintenance',        [MaintenanceController::class, 'store'])->name('maintenance.store');

        Route::get('profile',             [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile',             [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password',    [ProfileController::class, 'changePassword'])->name('profile.password');
    });
});

// Blog
Route::get('/blog', [App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/feed.xml', [App\Http\Controllers\BlogController::class, 'feed'])->name('blog.feed');
Route::get('/blog/category/{slug}', [App\Http\Controllers\BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{slug}', [App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');

// Docs
Route::get('/docs', [App\Http\Controllers\DocsController::class, 'index'])->name('docs');

// Sitemap
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Programmatic SEO
Route::prefix('best')->name('pseo.')->group(function () {
    Route::get('/{city}', [App\Http\Controllers\ProgrammaticSeoController::class, 'bestCity'])->name('best.city');
});
Route::prefix('kos-dekat')->name('pseo.')->group(function () {
    Route::get('/{kampus}', [App\Http\Controllers\ProgrammaticSeoController::class, 'bestKampus'])->name('best.kampus');
});
Route::get('/fasilitas/{fasilitas}', [App\Http\Controllers\ProgrammaticSeoController::class, 'kosFasilitas'])->name('pseo.fasilitas');
Route::get('/kos-{tipe}', [App\Http\Controllers\ProgrammaticSeoController::class, 'kosTipe'])->where('tipe', 'putra|putri|campur|harian|bulanan|tahunan')->name('pseo.tipe');
Route::get('/kos-{range}', [App\Http\Controllers\ProgrammaticSeoController::class, 'kosHarga'])->where('range', 'dibawah-500rb|500rb-1jt|1jt-2jt|diatas-2jt')->name('pseo.harga');
Route::get('/alternatif/{slug}', [App\Http\Controllers\ProgrammaticSeoController::class, 'alternatif'])->name('pseo.alternatif');
Route::get('/bandingkan/{slug}', [App\Http\Controllers\ProgrammaticSeoController::class, 'bandingkan'])->where('slug', '.*-vs-.*')->name('pseo.bandingkan');
Route::get('/beli-aplikasi-kos', [App\Http\Controllers\ProgrammaticSeoController::class, 'beliSourceCode'])->name('pseo.beli');

// License Pairing v3 routes
require base_path('routes/pair-routes.php');
