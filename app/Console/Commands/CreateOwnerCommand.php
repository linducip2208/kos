<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateOwnerCommand extends Command
{
    protected $signature   = 'koskosan:create-owner';
    protected $description = 'Wizard membuat akun owner pertama';

    public function handle(): int
    {
        $this->info('=== Setup Akun Owner Kos Manager ===');

        if (User::count() > 0) {
            if (!$this->confirm('Sudah ada user. Lanjutkan buat owner baru?')) {
                return Command::SUCCESS;
            }
        }

        $name     = $this->ask('Nama lengkap');
        $email    = $this->ask('Email');
        $password = $this->secret('Password (min 8 karakter)');

        if (strlen($password) < 8) {
            $this->error('Password minimal 8 karakter.');
            return Command::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error("Email '{$email}' sudah dipakai.");
            return Command::FAILURE;
        }

        $user = User::create([
            'name'      => $name,
            'email'     => $email,
            'password'  => Hash::make($password),
            'role'      => 'owner',
            'is_active' => true,
        ]);

        $this->info("Akun owner berhasil dibuat: {$user->email}");
        $this->line("Silakan login di: " . config('app.url') . '/admin');

        return Command::SUCCESS;
    }
}
