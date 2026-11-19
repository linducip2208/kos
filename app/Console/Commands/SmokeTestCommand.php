<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Occupant;
use App\Models\Property;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Throwable;

/**
 * Smoke-test untuk admin panel, portal penyewa, dan landing public.
 *
 *   php artisan app:smoke-test                 # all
 *   php artisan app:smoke-test --only=admin    # admin only
 *   php artisan app:smoke-test --only=portal
 *   php artisan app:smoke-test --only=public
 *   php artisan app:smoke-test --verbose-errors  # tampilkan stack trace lengkap
 */
class SmokeTestCommand extends Command
{
    protected $signature = 'app:smoke-test
        {--only= : admin|portal|public}
        {--verbose-errors : tampilkan stack trace untuk error}';

    protected $description = 'Hit semua URL admin/portal/landing untuk deteksi page yang rusak';

    private array $results = [];

    public function handle(): int
    {
        $only = $this->option('only');

        $this->newLine();
        $this->line('<fg=cyan>┌─────────────────────────────────────────────┐</>');
        $this->line('<fg=cyan>│  SMOKE TEST — Kos Manager Web Diagnostics   │</>');
        $this->line('<fg=cyan>└─────────────────────────────────────────────┘</>');

        if (! $only || $only === 'public') {
            $this->testPublic();
        }

        if (! $only || $only === 'admin') {
            $this->testAdmin();
        }

        if (! $only || $only === 'portal') {
            $this->testPortal();
        }

        return $this->printSummary();
    }

    /* ───────────────────────── public ───────────────────────── */

    private function testPublic(): void
    {
        $this->section('PUBLIC / LANDING');

        $urls = ['/'];
        if ($p = Property::where('is_active', true)->first()) {
            $urls[] = "/property/{$p->getRouteKey()}";
            $urls[] = "/booking/{$p->getRouteKey()}";
        }
        $urls[] = '/offline';
        $urls[] = '/admin/login';
        $urls[] = '/portal/login';

        foreach ($urls as $url) {
            $this->hitUrl('public', $url, null);
        }
    }

    /* ───────────────────────── admin ───────────────────────── */

    private function testAdmin(): void
    {
        $this->section('ADMIN PANEL');

        $admin = User::first();
        if (! $admin) {
            $this->results[] = ['admin', '—', '—', 'NO_USER', 'Tidak ada user admin di tabel users'];

            return;
        }

        $panel = Filament::getPanel('admin') ?? Filament::getDefaultPanel();

        // Semua custom pages (dashboard, laporan, settings, ...) — otomatis
        // tercakup saat ada page baru, tanpa daftar manual.
        foreach ($panel->getPages() as $page) {
            if (str_contains($page, '\\Auth\\')) {
                continue; // login/logout sudah dicakup di segmen public
            }
            try {
                $this->hitUrl('admin', $this->pathOf($page::getUrl(panel: $panel->getId())), $admin);
            } catch (Throwable $e) {
                $this->results[] = ['admin', class_basename($page), '—', 'SKIP', 'URL tidak bisa dibuat: '.substr($e->getMessage(), 0, 80)];
            }
        }

        // Semua resources: index + create + edit — otomatis tercakup saat ada
        // resource baru. getUrl() memakai route key model (mis. slug properti).
        foreach ($panel->getResources() as $resource) {
            $short = class_basename($resource);

            try {
                $this->hitUrl('admin', $this->pathOf($resource::getUrl('index', panel: $panel->getId())), $admin);
            } catch (Throwable $e) {
                $this->results[] = ['admin', $short.'::index', '—', 'SKIP', substr($e->getMessage(), 0, 80)];

                continue;
            }

            try {
                $this->hitUrl('admin', $this->pathOf($resource::getUrl('create', panel: $panel->getId())), $admin);
            } catch (Throwable $e) {
                $this->results[] = ['admin', $short.'::create', '—', 'SKIP', 'Tidak ada halaman create'];
            }

            try {
                $model = $resource::getModel();
                $rec = $model::first();
                if (! $rec) {
                    $this->results[] = ['admin', $short.'::edit', '—', 'SKIP', 'Belum ada record di '.class_basename($model)];

                    continue;
                }
                $this->hitUrl('admin', $this->pathOf($resource::getUrl('edit', ['record' => $rec], panel: $panel->getId())), $admin);
            } catch (Throwable $e) {
                $this->results[] = ['admin', $short.'::edit', '—', 'SKIP', 'Tidak ada halaman edit'];
            }
        }
    }

    private function pathOf(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        return $path ?: $url;
    }

    /* ───────────────────────── portal ───────────────────────── */

    private function testPortal(): void
    {
        $this->section('PORTAL PENYEWA');

        $occupant = Occupant::whereHas('leases', fn ($q) => $q->where('status', 'active'))->first()
                  ?? Occupant::first();

        if (! $occupant) {
            $this->results[] = ['portal', '—', '—', 'NO_USER', 'Tidak ada Occupant di database'];

            return;
        }

        $urls = [
            '/portal',
            '/portal/invoices',
            '/portal/maintenance',
            '/portal/maintenance/create',
            '/portal/profile',
        ];
        foreach ($urls as $url) {
            $this->hitUrl('portal', $url, $occupant, 'portal');
        }

        // /portal/invoices/{id} — pakai invoice milik occupant ini
        $invoice = Invoice::whereHas('lease', fn ($q) => $q->where('occupant_id', $occupant->id))->first();
        if ($invoice) {
            $this->hitUrl('portal', "/portal/invoices/{$invoice->id}", $occupant, 'portal');
        } else {
            $this->results[] = ['portal', '/portal/invoices/{id}', '—', 'SKIP', 'Occupant belum punya invoice'];
        }
    }

    /* ───────────────────────── runner ───────────────────────── */

    private function hitUrl(string $panel, string $url, $user, string $guard = 'web'): void
    {
        try {
            // Buat session bersih per request
            app('session.store')->flush();
            app('session.store')->regenerate();

            if ($user) {
                auth($guard)->login($user);
            }

            $request = Request::create($url, 'GET');
            $request->setLaravelSession(app('session.store'));

            $kernel = app(HttpKernel::class);
            $response = $kernel->handle($request);
            $code = $response->getStatusCode();

            if ($code >= 500) {
                $this->results[] = [$panel, $url, $code, 'FAIL', 'HTTP '.$code];
                $this->line("  <fg=red>✗ {$code}</> {$url}");
            } elseif ($code === 404) {
                // 404 = URL salah atau record tersaring scoping — harus FAIL agar ketahuan.
                $this->results[] = [$panel, $url, $code, 'FAIL', 'HTTP 404 Not Found'];
                $this->line("  <fg=red>✗ 404</> {$url}");
            } elseif ($code >= 400) {
                $this->results[] = [$panel, $url, $code, 'WARN', 'HTTP '.$code];
                $this->line("  <fg=yellow>! {$code}</> {$url}");
            } else {
                $this->results[] = [$panel, $url, $code, 'OK', null];
                $this->line("  <fg=green>✓ {$code}</> {$url}");
            }
        } catch (Throwable $e) {
            $msg = trim(str_replace(["\n", "\r"], ' ', substr($e->getMessage(), 0, 200)));
            $loc = basename($e->getFile()).':'.$e->getLine();
            $this->results[] = [$panel, $url, '500', 'EXCEPTION', class_basename($e)." @ {$loc} → {$msg}"];
            $this->line("  <fg=red>✗ EXC</> {$url}");
            $this->line('        <fg=red>'.class_basename($e)." @ {$loc}</>");
            $this->line("        <fg=red>{$msg}</>");
            if ($this->option('verbose-errors')) {
                $this->line('        <fg=gray>'.substr($e->getTraceAsString(), 0, 1500).'</>');
            }
        }
    }

    private function section(string $name): void
    {
        $this->newLine();
        $this->line("<fg=cyan>━━━ {$name} ━━━</>");
    }

    private function printSummary(): int
    {
        $this->newLine();
        $this->line('<fg=cyan>━━━ SUMMARY ━━━</>');

        $stats = ['OK' => 0, 'WARN' => 0, 'FAIL' => 0, 'EXCEPTION' => 0, 'SKIP' => 0, 'NO_USER' => 0];
        $errors = [];

        foreach ($this->results as $r) {
            $stats[$r[3]] = ($stats[$r[3]] ?? 0) + 1;
            if (in_array($r[3], ['FAIL', 'EXCEPTION', 'WARN', 'NO_USER'], true)) {
                $errors[] = $r;
            }
        }

        $this->line(sprintf(
            '  <fg=green>OK: %d</>   <fg=yellow>WARN: %d</>   <fg=red>FAIL: %d</>   <fg=red>EXC: %d</>   <fg=gray>SKIP: %d</>',
            $stats['OK'], $stats['WARN'], $stats['FAIL'], $stats['EXCEPTION'], $stats['SKIP'],
        ));

        if (empty($errors)) {
            $this->newLine();
            $this->info('  ✓ Semua page sehat!');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->line('<fg=red>━━━ MASALAH DITEMUKAN ━━━</>');
        $this->table(
            ['Panel', 'URL', 'Code', 'Status', 'Detail'],
            array_map(fn ($r) => [$r[0], $r[1], $r[2], $r[3], substr($r[4] ?? '', 0, 100)], $errors),
        );

        return self::FAILURE;
    }
}
