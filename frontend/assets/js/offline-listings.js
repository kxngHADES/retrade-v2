const OFFLINE_LISTING_DB = 'retrade-offline-listings';
const OFFLINE_LISTING_STORE = 'pendingListings';

function openOfflineListingDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(OFFLINE_LISTING_DB, 1);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains(OFFLINE_LISTING_STORE)) {
                db.createObjectStore(OFFLINE_LISTING_STORE, { keyPath: 'id' });
            }
        };

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

async function savePendingListing(listing) {
    const db = await openOfflineListingDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(OFFLINE_LISTING_STORE, 'readwrite');
        const store = tx.objectStore(OFFLINE_LISTING_STORE);
        const request = store.put(listing);
        request.onsuccess = () => resolve(listing);
        request.onerror = () => reject(request.error);
    });
}

async function getPendingListings() {
    const db = await openOfflineListingDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(OFFLINE_LISTING_STORE, 'readonly');
        const store = tx.objectStore(OFFLINE_LISTING_STORE);
        const request = store.getAll();
        request.onsuccess = () => resolve(request.result || []);
        request.onerror = () => reject(request.error);
    });
}

async function deletePendingListing(id) {
    const db = await openOfflineListingDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(OFFLINE_LISTING_STORE, 'readwrite');
        const store = tx.objectStore(OFFLINE_LISTING_STORE);
        const request = store.delete(id);
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
    });
}

function makeOfflineId() {
    return `${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;
}

function makeSafeName(value) {
    if (!value) return 'listing';
    return value.toString().trim().toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '') || 'listing';
}

function dataUrlToBlob(dataUrl) {
    const [meta, raw] = dataUrl.split(',');
    const matches = meta.match(/data:(.+);base64/);
    const mime = matches ? matches[1] : 'application/octet-stream';
    const binary = atob(raw);
    const buffer = new Uint8Array(binary.length);
    for (let i = 0; i < binary.length; i += 1) {
        buffer[i] = binary.charCodeAt(i);
    }
    return new Blob([buffer], { type: mime });
}

function fileToDataUrl(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = () => reject(reader.error);
        reader.readAsDataURL(file);
    });
}

async function uploadDataUrlToMinio(dataUrl, path) {
    if (typeof uploadToMinio !== 'function') {
        throw new Error('uploadToMinio is not available for offline sync.');
    }
    const blob = dataUrlToBlob(dataUrl);
    return uploadToMinio(blob, path);
}

async function postPendingListing(url, payload) {
    const formData = new FormData();
    Object.entries(payload).forEach(([key, value]) => {
        if (Array.isArray(value)) {
            formData.append(key, JSON.stringify(value));
            return;
        }
        formData.append(key, value === undefined || value === null ? '' : value);
    });

    const response = await fetch(url, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    });

    if (!response.ok) {
        throw new Error(`Sync request failed with status ${response.status}`);
    }

    return response;
}

async function buildSyncPayload(item) {
    const payload = { ...item.fields };

    if (item.thumbnailDataUrl) {
        const safeName = makeSafeName(item.fields.name || item.fields.title || 'listing');
        payload.thumbnail_url = await uploadDataUrlToMinio(item.thumbnailDataUrl, `${item.uid}/${safeName}_thumbnail_${item.id}.avif`);
    }

    if (Array.isArray(item.imageDataUrls) && item.imageDataUrls.length) {
        payload.list_of_image_url = [];
        const safeName = makeSafeName(item.fields.name || item.fields.title || 'listing');
        for (let i = 0; i < item.imageDataUrls.length; i += 1) {
            const path = `${item.uid}/${safeName}/${item.id}_${i}.avif`;
            const uploadedUrl = await uploadDataUrlToMinio(item.imageDataUrls[i], path);
            if (uploadedUrl) {
                payload.list_of_image_url.push(uploadedUrl);
            }
        }
    }

    return payload;
}

async function syncPendingListings() {
    if (!navigator.onLine) {
        return;
    }

    const items = await getPendingListings();
    if (items.length === 0) {
        return;
    }

    for (const item of items) {
        try {
            const payload = await buildSyncPayload(item);
            const url = item.type === 'update'
                ? `/pages/my-listings/edit-listing/?id=${encodeURIComponent(item.listingId)}`
                : '/pages/my-listings/create-listing/';

            await postPendingListing(url, payload);
            await deletePendingListing(item.id);
        } catch (error) {
            console.warn('Failed to sync offline listing', item.id, error);
        }
    }

    renderPendingOfflineListings();
}

window.saveOfflineListing = async function (options = {}) {
    if (!options || !options.type || !options.uid || !options.fields) {
        throw new Error('Invalid offline listing data.');
    }

    const item = {
        id: makeOfflineId(),
        type: options.type,
        uid: options.uid,
        listingId: options.listingId || null,
        fields: options.fields,
        thumbnailDataUrl: null,
        imageDataUrls: [],
        createdAt: Date.now(),
        status: 'pending'
    };

    if (options.thumbnailFile) {
        item.thumbnailDataUrl = await fileToDataUrl(options.thumbnailFile);
    }

    if (Array.isArray(options.imageFiles) && options.imageFiles.length > 0) {
        const imageUrls = [];
        for (const file of options.imageFiles) {
            imageUrls.push(await fileToDataUrl(file));
        }
        item.imageDataUrls = imageUrls;
    }

    await savePendingListing(item);
    renderPendingOfflineListings();
    return item;
};

window.saveOfflineListingUpdate = async function (options = {}) {
    options.type = 'update';
    return window.saveOfflineListing(options);
};

async function renderPendingOfflineListings() {
    const container = document.getElementById('pending-offline-listings');
    if (!container) {
        return;
    }

    const items = await getPendingListings();
    container.innerHTML = '';

    if (items.length === 0) {
        container.style.display = 'none';
        return;
    }

    container.style.display = 'block';
    const title = document.createElement('div');
    title.className = 'offline-listings-header';
    title.innerHTML = '<strong>Pending offline listings</strong>: these items will sync automatically when you are online again.';
    container.appendChild(title);

    const list = document.createElement('div');
    list.className = 'offline-listings-list';

    items.forEach(item => {
        const card = document.createElement('div');
        card.className = 'offline-listings-card';
        const date = new Date(item.createdAt).toLocaleString();
        card.innerHTML = `
            <div class="offline-listing-meta">
                <strong>${item.fields.name || item.fields.title || 'Untitled listing'}</strong>
                <span>${item.type === 'update' ? 'Update' : 'Create'} saved offline</span>
            </div>
            <div class="offline-listing-details">
                <span>${item.fields.price ? `R ${parseFloat(item.fields.price).toFixed(2)}` : 'No price'}</span>
                <span>${item.fields.location || 'No location'}</span>
                <span>${date}</span>
            </div>
        `;
        list.appendChild(card);
    });

    container.appendChild(list);
}

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch((error) => {
            console.warn('Service Worker registration failed:', error);
        });
    });
}

window.addEventListener('online', () => {
    syncPendingListings().catch(() => {});
});

document.addEventListener('DOMContentLoaded', () => {
    renderPendingOfflineListings().catch(() => {});
    syncPendingListings().catch(() => {});
});
