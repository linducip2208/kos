<?php

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Property;

class SitemapBuilder
{
    public function allUrls(): array
    {
        $base = rtrim(config('app.url'), '/');
        $urls = [];

        // Static pages
        $urls[] = $base . '/';
        $urls[] = $base . '/docs';
        $urls[] = $base . '/blog';
        $urls[] = $base . '/blog/feed.xml';

        // Blog posts
        foreach (BlogPost::published()->pluck('slug') as $slug) {
            $urls[] = $base . '/blog/' . $slug;
        }

        // Properties
        foreach (Property::active()->pluck('slug') as $slug) {
            if ($slug) {
                $urls[] = $base . '/property/' . $slug;
                $urls[] = $base . '/alternatif/' . $slug;
            }
        }

        // ───── PSEO 1: Best city ─────
        foreach (PseoDataService::cities() as $city) {
            $urls[] = $base . '/best/' . $city;
            $urls[] = $base . '/kos-di-' . $city;
        }

        // ───── PSEO 2: Campuses ─────
        foreach (PseoDataService::campuses() as $kampus) {
            $urls[] = $base . '/kos-dekat/' . $kampus;
        }

        // ───── PSEO 3: Facilities ─────
        foreach (PseoDataService::facilities() as $f) {
            $urls[] = $base . '/fasilitas/' . $f;
        }

        // ───── PSEO 4: Price ranges ─────
        foreach (PseoDataService::priceRanges() as $key => $range) {
            $urls[] = $base . '/kos-harga-' . $key;
        }

        // ───── PSEO 5: Types ─────
        foreach (PseoDataService::types() as $type) {
            $urls[] = $base . '/kos-untuk-' . $type;
        }

        // ───── PSEO 6: City × Facility combo — BIGGEST generator ─────
        $sampleCities = array_slice(PseoDataService::cities(), 0, 100); // top 100 cities
        $facilities = array_slice(PseoDataService::facilities(), 0, 12);  // top 12 facilities
        foreach ($sampleCities as $city) {
            foreach ($facilities as $facility) {
                $urls[] = $base . '/kos-di-' . $city . '-' . $facility;
            }
        }

        // ───── PSEO 7: City × Price combo ─────
        $priceKeys = array_keys(PseoDataService::priceRanges());
        foreach ($sampleCities as $city) {
            foreach ($priceKeys as $range) {
                $urls[] = $base . '/kos-di-' . $city . '-' . $range;
            }
        }

        // ───── PSEO 8: City × Type combo ─────
        foreach ($sampleCities as $city) {
            foreach (PseoDataService::types() as $type) {
                $urls[] = $base . '/kos-di-' . $city . '-untuk-' . $type;
            }
        }

        // ───── PSEO 9: Campus × City combo ─────
        $sampleCampuses = array_slice(PseoDataService::campuses(), 0, 30);
        foreach ($sampleCampuses as $kampus) {
            foreach (array_slice($sampleCities, 0, 30) as $city) {
                $urls[] = $base . '/kos-dekat-' . $kampus . '-di-' . $city;
            }
        }

        // ───── PSEO 10: Source code selling ─────
        $urls[] = $base . '/beli-aplikasi-kos';
        $urls[] = $base . '/bandingkan';
        foreach ($sampleCities as $city) {
            $urls[] = $base . '/beli-aplikasi-kos/di/' . $city;
        }
        foreach ($facilities as $feature) {
            $urls[] = $base . '/beli-aplikasi-kos/fitur/' . $feature;
            $urls[] = $base . '/source-code-kos/' . $feature;
        }

        // ───── PSEO 11: Comparisons between properties ─────
        $propertySlugs = Property::active()->pluck('slug')->filter()->toArray();
        if (count($propertySlugs) >= 2) {
            for ($i = 0; $i < min(count($propertySlugs), 10); $i++) {
                for ($j = $i + 1; $j < min(count($propertySlugs), 10); $j++) {
                    $urls[] = $base . '/bandingkan/' . $propertySlugs[$i] . '-vs-' . $propertySlugs[$j];
                }
            }
        }

        return array_values(array_unique($urls));
    }
}
