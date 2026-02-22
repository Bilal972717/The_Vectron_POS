const CACHE_NAME = "The_Vectron_POS-pwa-v1";
const STATIC_ASSETS = [
  "/",
  "/favicon.ico",
  "/css/app.css",
  "/js/app.js"
];

// Install event – cache static files
self.addEventListener("install", event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
  );
});

// Activate
self.addEventListener("activate", event => {
  event.waitUntil(self.clients.claim());
});

// Fetch event – serve from cache if offline
self.addEventListener("fetch", event => {
  if (event.request.method !== "GET") return;

  event.respondWith(
    caches.match(event.request).then(cachedResponse => {
      return cachedResponse || fetch(event.request).then(networkResponse => {
        return caches.open(CACHE_NAME).then(cache => {
          cache.put(event.request, networkResponse.clone());
          return networkResponse;
        });
      });
    })
  );
});