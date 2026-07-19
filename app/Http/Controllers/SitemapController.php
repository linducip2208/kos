<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Property;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = Cache::remember('sitemap_xml', now()->addHours(24), function () {
            return $this->buildUrls();
        });

        return response()->view('pseo.sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }

    protected function buildUrls(): array
    {
        $base = rtrim(config('app.url'), '/');
        $urls = [];

        $urls[] = ['loc' => $base . '/', 'priority' => '1.0', 'changefreq' => 'daily'];
        $urls[] = ['loc' => $base . '/docs', 'priority' => '0.8', 'changefreq' => 'weekly'];
        $urls[] = ['loc' => $base . '/blog', 'priority' => '0.9', 'changefreq' => 'daily'];

        foreach (BlogPost::published()->get() as $post) {
            $urls[] = [
                'loc' => $base . '/blog/' . $post->slug,
                'priority' => '0.7',
                'changefreq' => 'weekly',
                'lastmod' => $post->updated_at->toAtomString(),
            ];
        }

        foreach (Property::active()->get() as $p) {
            $urls[] = [
                'loc' => $base . '/property/' . $p->slug,
                'priority' => '0.9',
                'changefreq' => 'weekly',
            ];
        }

        $kota = ['jakarta', 'bandung', 'surabaya', 'semarang', 'yogyakarta', 'malang',
            'medan', 'makassar', 'palembang', 'depok', 'tangerang', 'bekasi',
            'bogor', 'denpasar', 'balikpapan', 'manado', 'pekanbaru', 'padang',
            'solo', 'purwokerto', 'cirebon', 'tasikmalaya', 'magelang', 'salatiga'];

        foreach ($kota as $k) {
            $urls[] = ['loc' => $base . '/best/' . $k, 'priority' => '0.7', 'changefreq' => 'weekly'];
        }

        $harga = ['dibawah-500rb', '500rb-1jt', '1jt-2jt', 'diatas-2jt'];
        foreach ($harga as $h) {
            $urls[] = ['loc' => $base . '/kos-' . $h, 'priority' => '0.6', 'changefreq' => 'weekly'];
        }

        $urls[] = ['loc' => $base . '/beli-aplikasi-kos', 'priority' => '0.5', 'changefreq' => 'monthly'];

        return $urls;
    }
}
