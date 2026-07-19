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

        $urls[] = $base . '/';
        $urls[] = $base . '/docs';
        $urls[] = $base . '/blog';
        $urls[] = $base . '/blog/feed.xml';

        foreach (BlogPost::published()->pluck('slug') as $slug) {
            $urls[] = $base . '/blog/' . $slug;
        }

        foreach (Property::active()->pluck('slug') as $slug) {
            if ($slug) {
                $urls[] = $base . '/property/' . $slug;
            }
        }

        $kota = [
            'jakarta', 'bandung', 'surabaya', 'semarang', 'yogyakarta', 'malang',
            'medan', 'makassar', 'palembang', 'depok', 'tangerang', 'bekasi',
            'bogor', 'denpasar', 'balikpapan', 'manado', 'pekanbaru', 'padang',
            'solo', 'purwokerto', 'cirebon', 'tasikmalaya', 'magelang', 'salatiga',
        ];

        foreach ($kota as $k) {
            $urls[] = $base . '/best/' . $k;
        }

        $kampus = ['ui', 'itb', 'ugm', 'unair', 'undip', 'ub', 'its', 'unpad', 'ipb', 'uns', 'unnes'];
        foreach ($kampus as $k) {
            $urls[] = $base . '/kos-dekat/' . $k;
        }

        $fasilitas = ['ac', 'wifi', 'kamar-mandi-dalam', 'dapur', 'parkir-motor', 'parkir-mobil'];
        foreach ($fasilitas as $f) {
            $urls[] = $base . '/fasilitas/' . $f;
        }

        $harga = ['dibawah-500rb', '500rb-1jt', '1jt-2jt', 'diatas-2jt'];
        foreach ($harga as $h) {
            $urls[] = $base . '/kos-' . $h;
        }

        $urls[] = $base . '/beli-aplikasi-kos';

        return array_values(array_unique($urls));
    }
}
