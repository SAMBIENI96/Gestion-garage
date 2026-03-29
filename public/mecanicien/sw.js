const CACHE_NAME = 'autogest-v1';
const ASSETS = [
  '/mecanicien/',
  '/mecanicien/index.html',
  '/mecanicien/manifest.json',
];

// Installation
self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS))
  );
  self.skipWaiting();
});

// Activation
self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

// Fetch — Network first, cache fallback
self.addEventListener('fetch', e => {
  const url = new URL(e.request.url);

  // API calls — toujours réseau, jamais cache
  if (url.pathname.startsWith('/api/')) {
    e.respondWith(fetch(e.request).catch(() =>
      new Response(JSON.stringify({ error: 'Pas de connexion' }), {
        headers: { 'Content-Type': 'application/json' }
      })
    ));
    return;
  }

  // Assets statiques — cache first
  e.respondWith(
    caches.match(e.request).then(cached =>
      cached || fetch(e.request).then(response => {
        const clone = response.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(e.request, clone));
        return response;
      }).catch(() => caches.match('/mecanicien/index.html'))
    )
  );
});
