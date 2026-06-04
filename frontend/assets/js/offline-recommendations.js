const OFFLINE_RECOMMENDATIONS_KEY = 'retrade-offline-recommendations';

function saveOfflineRecommendations(listings) {
    try {
        const payload = {
            items: listings.map(item => ({
                _id: item['_id'] || item.id || '',
                name: item['name'] || '',
                price: item['price'] ?? 0,
                location: item['location'] || '',
                category: item['category'] || '',
                condition: item['condition'] || '',
                delivery_method: item['delivery_method'] || '',
                thumbnail_url: item['thumbnail_url'] || '',
                tags: item['tags'] || []
            })),
            cachedAt: Date.now()
        };
        localStorage.setItem(OFFLINE_RECOMMENDATIONS_KEY, JSON.stringify(payload));
    } catch (error) {
        console.warn('Unable to save offline recommendations:', error);
    }
}

function getOfflineRecommendations() {
    try {
        const raw = localStorage.getItem(OFFLINE_RECOMMENDATIONS_KEY);
        if (!raw) return [];
        const parsed = JSON.parse(raw);
        return Array.isArray(parsed.items) ? parsed.items : [];
    } catch (error) {
        console.warn('Unable to read offline recommendations:', error);
        return [];
    }
}

function createHomeCard(item) {
    const card = document.createElement('a');
    card.href = `/view/?listing_id=${encodeURIComponent(item._id)}`;
    card.className = 'home-card';
    card.setAttribute('data-index', '0');

    const imageWrap = document.createElement('div');
    imageWrap.className = 'home-card-image';

    const img = document.createElement('img');
    img.className = 'lazy-img';
    img.alt = item.name ? item.name : 'Listing';
    img.src = '/assets/placeholder.jpg';
    img.dataset.src = item.thumbnail_url || '/assets/placeholder.jpg';
    img.loading = 'lazy';
    imageWrap.appendChild(img);

    const content = document.createElement('div');
    content.className = 'home-card-content';

    const title = document.createElement('h3');
    title.className = 'home-card-title';
    title.textContent = item.name || 'Listing';
    content.appendChild(title);

    const price = document.createElement('p');
    price.className = 'home-card-price';
    price.textContent = `R ${Number(item.price || 0).toFixed(2)}`;
    content.appendChild(price);

    if (item.location) {
        const meta = document.createElement('div');
        meta.className = 'home-card-meta';
        meta.innerHTML = `
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 21s-6-5.686-6-10a6 6 0 1 1 12 0c0 4.314-6 10-6 10z" fill="none" stroke="currentColor" stroke-width="2" />
                <circle cx="12" cy="11" r="2" fill="currentColor" />
            </svg>
            <span>${item.location}</span>
        `;
        content.appendChild(meta);
    }

    card.appendChild(imageWrap);
    card.appendChild(content);

    return card;
}

function renderOfflineRecommendations(items) {
    const container = document.getElementById('listing-container');
    if (!container) return;

    if (!items.length) return;

    container.innerHTML = '';
    items.forEach(item => {
        const card = createHomeCard(item);
        container.appendChild(card);
    });
}

function lazyLoadImages() {
    const lazyImages = document.querySelectorAll('img.lazy-img');
    if (!lazyImages.length) return;

    const loadImage = (img) => {
        const src = img.dataset.src;
        if (!src || !navigator.onLine) {
            return;
        }
        img.src = src;
        img.removeAttribute('data-src');
    };

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    loadImage(entry.target);
                    obs.unobserve(entry.target);
                }
            });
        }, {
            rootMargin: '120px 0px',
            threshold: 0.1
        });

        lazyImages.forEach(img => observer.observe(img));
    } else {
        lazyImages.forEach(loadImage);
    }
}

window.addEventListener('online', () => {
    lazyLoadImages();
});

function initOfflineRecommendations() {
    let listings = [];
    if (window.recommendationListings && Array.isArray(window.recommendationListings)) {
        listings = window.recommendationListings;
    }

    if (navigator.onLine && listings.length) {
        saveOfflineRecommendations(listings);
    }

    if (!navigator.onLine) {
        const offlineItems = getOfflineRecommendations();
        if (offlineItems.length) {
            renderOfflineRecommendations(offlineItems);
        }
    }

    lazyLoadImages();
}

window.addEventListener('load', () => {
    initOfflineRecommendations();
});
