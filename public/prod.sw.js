const CACHE_NAME = 'antrian-loket-v2';

// Daftar aset statis dan rute utama yang WAJIB bisa dibuka saat offline
const assetsToCache = [
    '/',
    '/kasir',
    '/display',
    '/manifest.json',
    '/js/pusher.min.js',
    
    // Library CDN yang kita pakai agar tersimpan di lokal browser setelah diload sekali
    // 'https://js.pusher.com/8.2.0/pusher.min.js'
];

// 1. EVENT: INSTALL (Mendownload & menyimpan aset ke dalam cache browser)
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[Service Worker] Mengamankan aset ke dalam Cache...');
            return cache.addAll(assetsToCache);
        }).then(() => self.skipWaiting()) // Paksa SW baru langsung aktif
    );
});

// 2. EVENT: ACTIVATE (Membersihkan cache versi lama jika ada update codingan)
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        console.log('[Service Worker] Menghapus Cache Usang:', cache);
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. EVENT: FETCH (Mencegat request network. Jika offline, ambilkan dari Cache)
self.addEventListener('fetch', (event) => {
    // Abaikan request API (/api/...) agar data antrian tetap dinamis mencari server riil
    if (event.request.url.includes('/api/')) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            // Jika aset ada di cache, langsung kembalikan (Sangat cepat & mendukung offline)
            if (cachedResponse) {
                return cachedResponse;
            }

            // Jika tidak ada di cache, ambil via internet/jaringan lokal normal
            return fetch(event.request).then((networkResponse) => {
                // Opsional: Simpan aset baru yang ditemui di jalan ke dalam cache secara otomatis
                if (networkResponse && networkResponse.status === 200 && event.request.method === 'GET') {
                    const responseToCache = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }
                return networkResponse;
            }).catch(() => {
                // Fallback jika benar-benar offline dan aset tidak ada di cache sama sekali
                console.error('[Service Worker] Gagal memuat aset, device sedang offline.');
            });
        })
    );
});