<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Property;
use App\Services\Seo\PseoDataService;
use App\Services\Seo\SitemapBuilder;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = Cache::remember('sitemap_xml_v2', now()->addHours(6), function () {
            return $this->buildUrls();
        });

        return response()->view('pseo.sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }

    protected function buildUrls(): array
    {
        $base = rtrim(config('app.url'), '/');
        $urls = [];
        $now = now()->toAtomString();

        $add = function ($loc, $prio = '0.7', $freq = 'weekly') use (&$urls, $now) {
            $urls[] = ['loc' => $loc, 'priority' => $prio, 'changefreq' => $freq, 'lastmod' => $now];
        };

        // Static
        $add($base . '/', '1.0', 'daily');
        $add($base . '/docs', '0.9', 'weekly');
        $add($base . '/blog', '0.9', 'daily');

        // Blog posts
        foreach (BlogPost::published()->get() as $post) {
            $urls[] = [
                'loc' => $base . '/blog/' . $post->slug,
                'priority' => '0.7',
                'changefreq' => 'weekly',
                'lastmod' => $post->updated_at->toAtomString(),
            ];
        }

        // Properties
        foreach (Property::active()->get() as $p) {
            $add($base . '/property/' . $p->slug, '0.9', 'weekly');
            $add($base . '/alternatif/' . $p->slug, '0.6');
        }

        // Best city
        foreach (PseoDataService::cities() as $city) {
            $add($base . '/best/' . $city, '0.7');
            $add($base . '/kos-di-' . $city, '0.7');
        }

        // Campuses
        foreach (PseoDataService::campuses() as $kampus) {
            $add($base . '/kos-dekat/' . $kampus, '0.7');
        }

        // Facilities
        foreach (PseoDataService::facilities() as $f) {
            $add($base . '/fasilitas/' . $f, '0.6');
        }

        // Price ranges
        foreach (array_keys(PseoDataService::priceRanges()) as $key) {
            $add($base . '/kos-harga-' . $key, '0.6');
        }

        // Types
        foreach (PseoDataService::types() as $type) {
            $add($base . '/kos-untuk-' . $type, '0.6');
        }

        // City combos: facility (top cities × top facilities)
        $sampleCities = array_slice(PseoDataService::cities(), 0, 100);
        $topFacilities = array_slice(PseoDataService::facilities(), 0, 12);
        foreach ($sampleCities as $city) {
            foreach ($topFacilities as $fac) {
                $add($base . '/kos-di-' . $city . '-' . $fac, '0.5');
            }
            foreach (array_keys(PseoDataService::priceRanges()) as $range) {
                $add($base . '/kos-di-' . $city . '-' . $range, '0.5');
            }
            foreach (PseoDataService::types() as $type) {
                $add($base . '/kos-di-' . $city . '-untuk-' . $type, '0.5');
            }
        }

        // Campus × City combos
        $sampleCampuses = array_slice(PseoDataService::campuses(), 0, 30);
        foreach ($sampleCampuses as $kampus) {
            foreach (array_slice($sampleCities, 0, 30) as $city) {
                $add($base . '/kos-dekat-' . $kampus . '-di-' . $city, '0.5');
            }
        }

        // Source code selling
        $add($base . '/beli-aplikasi-kos', '0.5', 'monthly');
        $add($base . '/bandingkan', '0.5', 'monthly');
        foreach ($sampleCities as $city) {
            $add($base . '/beli-aplikasi-kos/di/' . $city, '0.4');
        }
        foreach ($topFacilities as $feature) {
            $add($base . '/beli-aplikasi-kos/fitur/' . $feature, '0.4');
            $add($base . '/source-code-kos/' . $feature, '0.4');
        }

        return $urls;
    }
}
