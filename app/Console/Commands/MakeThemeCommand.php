<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeThemeCommand extends Command
{
    protected $signature   = 'make:theme {area : admin|user|frontend} {slug : Slug theme, contoh: dark-pro}';
    protected $description = 'Buat scaffold theme baru di folder themes/{area}/{slug}/';

    public function handle(): int
    {
        $area     = $this->argument('area');
        $slug     = Str::slug($this->argument('slug'), '-');
        $name     = Str::title(str_replace('-', ' ', $slug));
        $themeDir = base_path("themes/{$area}/{$slug}");

        if (!in_array($area, ['admin', 'user', 'frontend'])) {
            $this->error("Area tidak valid. Pilih: admin, user, atau frontend.");
            return Command::FAILURE;
        }

        if (is_dir($themeDir)) {
            $this->error("Theme '{$slug}' untuk area '{$area}' sudah ada.");
            return Command::FAILURE;
        }

        $this->createStructure($themeDir, $area, $slug, $name);
        $this->info("Theme '{$slug}' untuk area '{$area}' berhasil dibuat di themes/{$area}/{$slug}/");

        return Command::SUCCESS;
    }

    private function createStructure(string $dir, string $area, string $slug, string $name): void
    {
        $baseDirs = ['assets/css', 'assets/js'];

        if ($area === 'frontend' || $area === 'user') {
            $baseDirs = array_merge($baseDirs, [
                'views/layouts', 'views/pages', 'views/components',
            ]);
        }

        foreach ($baseDirs as $d) {
            mkdir("{$dir}/{$d}", 0755, true);
        }

        // theme.json
        file_put_contents("{$dir}/theme.json", json_encode([
            'name'          => $name,
            'slug'          => $slug,
            'area'          => $area,
            'version'       => '1.0.0',
            'description'   => '',
            'author'        => 'whitelabel.co.id',
            'primary_color' => '#6366f1',
        ], JSON_PRETTY_PRINT));

        // CSS stub
        file_put_contents("{$dir}/assets/css/theme.css", "/* {$name} Theme — area: {$area} */\n");
        file_put_contents("{$dir}/assets/js/theme.js",  "/* {$name} Theme JS */\n");

        // Layout untuk frontend/user
        if (in_array($area, ['frontend', 'user'])) {
            file_put_contents("{$dir}/views/layouts/app.blade.php", $this->layoutStub($name));
            file_put_contents("{$dir}/views/pages/home.blade.php", "@extends('{$slug}::layouts.app')\n@section('content')\n<p>Home</p>\n@endsection\n");
        }
    }

    private function layoutStub(string $name): string
    {
        return <<<BLADE
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    <link rel="stylesheet" href="{{ asset('themes/css/theme.css') }}">
    @stack('styles')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
BLADE;
    }
}
