const CACHE_NAME = 'retrade-static-v1';
const DB_NAME = 'retrade-db';
const STORE_NAME = 'api-cache';

const initDB = () => {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, 1);
        request.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains(STORE_NAME)) {
                db.createObjectStore(STORE_NAME, { keyPath: 'url' });
            }
        };
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
};

const cacheToIndexedDB = async (url, responseText, contentType) => {
    try {
        const db = await initDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            store.put({ url, data: responseText, contentType, timestamp: Date.now() });
            tx.oncomplete = () => resolve();
            tx.onerror = () => reject(tx.error);
        });
    } catch (err) {
        console.error('IDB Cache Failed:', err);
    }
};

const getFromIndexedDB = async (url) => {
    try {
        const db = await initDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readonly');
            const store = tx.objectStore(STORE_NAME);
            const req = store.get(url);
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
    } catch (err) {
        return null;
    }
};

self.addEventListener('install', event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll([
                '/',
                '/search.php',
                '/manifest.json',
                '/assets/js/index_listings.js',
                '/assets/js/upload.js',
                '/assets/js/offline-listings.js',
                '/assets/js/offline-recommendations.js',
                '/assets/css/variables.css',
                '/assets/css/global.css',
                '/assets/css/listing.css'
            ]);
        })
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);
    const isPageOrApi = event.request.headers.get('accept').includes('text/html') || 
                        event.request.headers.get('accept').includes('application/json') ||
                        url.pathname.endsWith('.php');

    if (isPageOrApi) {
        event.respondWith(
            fetch(event.request)
                .then(async response => {
                    const clonedResp = response.clone();
                    if (clonedResp.ok) {
                        const contentType = clonedResp.headers.get('content-type') || '';
                        clonedResp.text().then(text => {
                            cacheToIndexedDB(event.request.url, text, contentType);
                        });
                    }
                    return response;
                })
                .catch(async () => {
                    const cached = await getFromIndexedDB(event.request.url);
                    if (cached && cached.data) {
                        return new Response(cached.data, {
                            headers: { 'Content-Type': cached.contentType }
                        });
                    }
                    const cacheResponse = await caches.match(event.request);
                    if (cacheResponse) {
                        return cacheResponse;
                    }
                    return new Response("Network error", { status: 503, statusText: "Service Unavailable" });
                })
        );
    } else {
        event.respondWith(
            caches.match(event.request).then(response => {
                return response || fetch(event.request);
            })
        );
    }
});