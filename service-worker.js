const CACHE_NAME = 'laundry-app-v3';
const APP_SHELL = [
  './manifest.json',
  './assets/css/bootstrap.min.css',
  './assets/css/all.min.css',
  './assets/css/fonts.css',
  './assets/js/bootstrap.bundle.min.js',
  './assets/image/logo.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((key) => key !== CACHE_NAME)
          .map((key) => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') {
    return;
  }

  const requestUrl = new URL(event.request.url);
  const isSameOrigin = requestUrl.origin === self.location.origin;
  const requestDestination = event.request.destination;
  const isStaticAssetRequest =
    ['style', 'script', 'image', 'font'].includes(requestDestination) ||
    requestUrl.pathname.startsWith('/assets/');

  // Jangan cache halaman dinamis/CRUD. Hanya aset statis same-origin yang boleh di-cache.
  if (!isSameOrigin || !isStaticAssetRequest) {
    return;
  }

  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      if (cachedResponse) {
        return cachedResponse;
      }

      return fetch(event.request).then((networkResponse) => {
        if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
          return networkResponse;
        }

        const responseToCache = networkResponse.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, responseToCache));
        return networkResponse;
      });
    })
  );
});
