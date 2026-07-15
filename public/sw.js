const CACHE = 'sans-family-v1';
const ASSETS = [
    '/css/app.css',
    '/js/app.js',
    '/manifest.webmanifest',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open(CACHE)
            .then((cache) => cache.addAll(ASSETS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

// Network-first: selalu ambil versi terbaru, cache hanya sebagai cadangan offline.
self.addEventListener('fetch', (e) => {
    if (e.request.method !== 'GET') return;

    e.respondWith(
        fetch(e.request).catch(() =>
            caches.match(e.request, { ignoreSearch: true }).then((hit) => hit || Response.error())
        )
    );
});
