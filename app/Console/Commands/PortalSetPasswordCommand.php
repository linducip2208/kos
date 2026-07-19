<?php

namespace App\Console\Commands;

use App\Models\Occupant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class PortalSetPasswordCommand extends Command
{
    protected $signature   = 'portal:set-password {phone? : Nomor HP penyewa} {password? : Password baru}';
    protected $description = 'Set atau reset password portal untuk penyewa';

    public function handle(): int
    {
        $phone = $this->argument('phone') ?? $this->ask('Nomor HP penyewa');

        $occupant = Occupant::where('phone', $phone)->first();
        if (!$occupant) {
            $this->error("Penyewa dengan nomor HP {$phone} tidak ditemukan.");
            return 1;
        }

        $this->line("Penyewa: <info>{$occupant->name}</info>");

        $password = $this->argument('password') ?? $this->secret('Password baru (min 6 karakter)');
        if (strlen($password) < 6) {
            $this->error('Password minimal 6 karakter.');
            return 1;
        }

        $occupant->update([
            'portal_password' => Hash::make($password),
            'portal_active'   => true,
        ]);

        $this->info("Password portal berhasil diset untuk {$occupant->name}.");
        $this->line("URL Portal: " . url('/portal/login'));
        return 0;
    }
}
