// Paksa hapus semua cache secara otomatis saat file ini dimuat browser
self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    console.log('[SW] Menghapus Cache secara permanen:', cache);
                    return caches.delete(cache);
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Kembalikan semua request langsung ke network server tanpa perantara cache
// self.addEventListener('fetch', (event) => {
//     return; // Bypass langsung
// });