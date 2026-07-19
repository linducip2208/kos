<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackupDatabaseCommand extends Command
{
    protected $signature   = 'backup:run {--only-db} {--keep=7 : Jumlah file backup yang disimpan}';
    protected $description = 'Backup database ke folder storage/backups';

    public function handle(): int
    {
        $dir = storage_path('backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $connection = config('database.default');
        $timestamp  = now()->format('Y-m-d_H-i-s');
        $filename   = "backup_{$timestamp}";

        match ($connection) {
            'sqlite' => $this->backupSqlite($dir, $filename),
            'mysql'  => $this->backupMysql($dir, $filename),
            default  => $this->backupSqlDump($dir, $filename, $connection),
        };

        $this->pruneOldBackups($dir, (int) $this->option('keep'));

        return Command::SUCCESS;
    }

    private function backupSqlite(string $dir, string $filename): void
    {
        $source = database_path(config('database.connections.sqlite.database'));
        $dest   = "{$dir}/{$filename}.sqlite";
        copy($source, $dest);
        $this->info("Backup SQLite: {$dest}");
    }

    private function backupMysql(string $dir, string $filename): void
    {
        $cfg  = config('database.connections.mysql');
        $dest = "{$dir}/{$filename}.sql";
        $cmd  = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
            escapeshellarg($cfg['host']),
            escapeshellarg($cfg['port']),
            escapeshellarg($cfg['username']),
            escapeshellarg($cfg['password']),
            escapeshellarg($cfg['database']),
            escapeshellarg($dest)
        );
        exec($cmd, $output, $code);

        if ($code !== 0) {
            $this->error('mysqldump gagal. Pastikan mysqldump tersedia di PATH.');
        } else {
            $this->info("Backup MySQL: {$dest}");
        }
    }

    private function backupSqlDump(string $dir, string $filename, string $connection): void
    {
        $dest   = "{$dir}/{$filename}.sql";
        $tables = DB::connection($connection)->select("SELECT name FROM sqlite_master WHERE type='table'");
        $sql    = '';

        foreach ($tables as $table) {
            $name = $table->name;
            $rows = DB::connection($connection)->table($name)->get();
            foreach ($rows as $row) {
                $values = collect((array) $row)->map(fn ($v) => is_null($v) ? 'NULL' : "'" . addslashes($v) . "'")->join(', ');
                $sql   .= "INSERT INTO `{$name}` VALUES ({$values});\n";
            }
        }

        file_put_contents($dest, $sql);
        $this->info("Backup dump: {$dest}");
    }

    private function pruneOldBackups(string $dir, int $keep): void
    {
        $files = glob("{$dir}/backup_*");
        if (!$files) return;

        usort($files, fn ($a, $b) => filemtime($b) - filemtime($a));
        $toDelete = array_slice($files, $keep);

        foreach ($toDelete as $file) {
            unlink($file);
            $this->line("Hapus backup lama: " . basename($file));
        }
    }
}
