<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class RemoveDemoAccountsCommand extends Command
{
    protected $signature = 'koskosan:remove-demo
        {--delete : Hapus permanen akun demo (default: nonaktifkan saja)}
        {--force : Jalankan tanpa konfirmasi}';

    protected $description = 'Nonaktifkan/hapus akun demo (@kos.test) sebelum go-live atau serah terima ke pembeli';

    public function handle(): int
    {
        $demos = User::where('email', 'like', '%@kos.test')->get();

        if ($demos->isEmpty()) {
            $this->info('Tidak ada akun demo (@kos.test). Sudah bersih.');

            return Command::SUCCESS;
        }

        $this->table(['Email', 'Nama', 'Role', 'Aktif'], $demos->map(fn (User $u) => [
            $u->email, $u->name, $u->role, $u->is_active ? 'ya' : 'tidak',
        ])->all());

        if (! $this->option('force') && ! $this->confirm("Proses {$demos->count()} akun demo di atas?", true)) {
            return Command::SUCCESS;
        }

        if ($this->option('delete')) {
            $count = $demos->count();
            User::where('email', 'like', '%@kos.test')->delete();
            $this->info("{$count} akun demo dihapus permanen.");
        } else {
            User::where('email', 'like', '%@kos.test')->update(['is_active' => false]);
            $this->info("{$demos->count()} akun demo dinonaktifkan (is_active=false).");
            $this->line('Gunakan --delete untuk menghapus permanen.');
        }

        $this->line('Buat akun owner baru: php artisan koskosan:create-owner');

        return Command::SUCCESS;
    }
}
