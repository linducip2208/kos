<?php

namespace App\Console\Commands;

use App\Services\Seo\IndexNowService;
use Illuminate\Console\Command;

class IndexNowSubmit extends Command
{
    protected $signature = 'seo:indexnow
                            {--url= : Submit a single URL}
                            {--new : Submit only new URLs since last run}
                            {--all : Submit all PSEO URLs}';

    protected $description = 'Submit URLs to IndexNow (Bing, Yandex, Seznam, Naver)';

    public function handle(IndexNowService $service): int
    {
        if ($url = $this->option('url')) {
            $this->info("Submitting single URL: {$url}");
            $results = $service->submitSingle($url);
            $this->table(['Engine', 'Success'], collect($results)->map(fn ($v, $k) => [$k, $v ? 'Yes' : 'No']));
            return self::SUCCESS;
        }

        if ($this->option('new')) {
            $this->info('Submitting new URLs only...');
            $results = $service->submitNewOnly([]); // will use SitemapBuilder internally
            $this->info('Done.');
            return self::SUCCESS;
        }

        // Default: submit all
        $this->info('Submitting all PSEO URLs...');
        $results = $service->submitAll();
        $this->info('Done. Submitted ' . count($results) . ' chunks.');
        return self::SUCCESS;
    }
}
