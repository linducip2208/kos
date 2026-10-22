/* Kos Manager Service Worker — cache aman untuk halaman publik.
 * - TIDAK menyentuh /admin, /api, /__pair, /livewire, /filament (selalu network).
 * - Navigasi publik: network-first → cache → halaman /offline.
 * - Aset statis (build, ikon, gambar, font): stale-while-revalidate.
 */
const VERSION = 'kos-v1';
const STATIC_CACHE = `km-static-${VERSION}`;
const PAGE_CACHE = `km-pages-${VERSION}`;
const OFFLINE_URL = '/offline';

const PRECACHE = [OFFLINE_URL, '/icons/icon-192.png', '/icons/icon-512.png', '/manifest.webmanifest'];
const BYPASS_PREFIXES = ['/admin', '/api', '/__pair', '/livewire', '/filament'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => ![STATIC_CACHE, PAGE_CACHE].includes(k)).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

function isBypassed(url) {
    return BYPASS_PREFIXES.some((p) => url.pathname.startsWith(p));
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin || isBypassed(url)) return;

    // Navigasi halaman publik: network-first.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((res) => {
                    const copy = res.clone();
                    caches.open(PAGE_CACHE).then((cache) => cache.put(request, copy));
                    return res;
                })
                .catch(() => caches.match(request).then((hit) => hit || caches.match(OFFLINE_URL)))
        );
        return;
    }

    // Aset statis: stale-while-revalidate.
    if (
        url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/') ||
        url.pathname.startsWith('/images/') || url.pathname.startsWith('/storage/') ||
        url.pathname.endsWith('.css') || url.pathname.endsWith('.js') ||
        url.pathname.endsWith('.woff2') || url.pathname.endsWith('.woff')
    ) {
        event.respondWith(
            caches.match(request).then((hit) => {
                const network = fetch(request).then((res) => {
                    if (res && res.ok) {
                        const copy = res.clone();
                        caches.open(STATIC_CACHE).then((cache) => cache.put(request, copy));
                    }
                    return res;
                }).catch(() => hit);
                return hit || network;
            })
        );
    }
});
