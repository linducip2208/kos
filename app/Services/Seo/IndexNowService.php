<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class IndexNowService
{
    protected array $engines = [
        'https://www.bing.com/indexnow',
        'https://yandex.com/indexnow',
        'https://search.seznam.cz/indexnow',
        'https://indexnow.naver.com/indexnow',
    ];

    protected string $keyFilePath;

    public function __construct()
    {
        $this->keyFilePath = public_path('indexnow-key.txt');
        $this->ensureKeyExists();
    }

    protected function ensureKeyExists(): void
    {
        if (!file_exists($this->keyFilePath)) {
            file_put_contents($this->keyFilePath, bin2hex(random_bytes(16)));
        }
    }

    public function getKey(): string
    {
        return trim(file_get_contents($this->keyFilePath));
    }

    public function submit(array $urls): array
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST);
        $results = [];

        foreach ($this->engines as $engine) {
            try {
                $response = Http::timeout(10)->post($engine, [
                    'host' => $host,
                    'key' => $this->getKey(),
                    'keyLocation' => config('app.url') . '/indexnow-key.txt',
                    'urlList' => $urls,
                ]);

                $results[$engine] = $response->successful();
            } catch (\Exception $e) {
                $results[$engine] = false;
            }
        }

        return $results;
    }

    public function submitAll(): array
    {
        $urls = app(SitemapBuilder::class)->allUrls();
        $chunks = array_chunk($urls, 5000);
        $results = [];

        foreach ($chunks as $chunk) {
            $results[] = $this->submit($chunk);
            sleep(1);
        }

        return $results;
    }

    public function submitNewOnly(array $urls): array
    {
        $submitted = Cache::get('indexnow_last_submit', []);
        $new = array_diff($urls, $submitted);

        if (empty($new)) {
            return [];
        }

        $result = $this->submit(array_values($new));

        $allSubmitted = array_unique(array_merge($submitted, $new));
        $allSubmitted = array_slice($allSubmitted, -50000);
        Cache::put('indexnow_last_submit', $allSubmitted, now()->addDays(30));

        return $result;
    }

    public function submitSingle(string $url): array
    {
        return $this->submit([$url]);
    }
}
