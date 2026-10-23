// Service worker AutoGest Atelier.
// Pages et scripts : réseau d'abord (toujours la dernière version), cache si hors ligne.
// Icônes et polices : cache d'abord. API : jamais mise en cache.
const CACHE = 'autogest-atelier-v4';
const PRECACHE = [
  '/mecanicien/',
  '/mecanicien/app.js?v=4',
  '/mecanicien/manifest.json',
  '/mecanicien/icons/icon.svg',
  '/mecanicien/icons/icon-192.png',
  '/mecanicien/icons/favicon-32.png',
];

self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(CACHE).then((c) => c.addAll(PRECACHE)));
  self.skipWaiting();
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (e) => {
  const req = e.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);

  // API et photos : toujours le réseau
  if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/storage/')) return;

  const sameApp = url.origin === location.origin && url.pathname.startsWith('/mecanicien');
  const isAsset = url.pathname.startsWith('/mecanicien/icons/') || url.hostname.endsWith('gstatic.com') || url.hostname.endsWith('googleapis.com');

  if (isAsset) {
    e.respondWith(caches.match(req).then((hit) => hit || fetch(req).then((res) => {
      const copy = res.clone();
      caches.open(CACHE).then((c) => c.put(req, copy));
      return res;
    })));
    return;
  }

  if (sameApp || req.mode === 'navigate') {
    e.respondWith(fetch(req).then((res) => {
      if (res.ok && sameApp) { const copy = res.clone(); caches.open(CACHE).then((c) => c.put(req, copy)); }
      return res;
    }).catch(() => caches.match(req, { ignoreSearch: true }).then((hit) => hit || caches.match('/mecanicien/'))));
  }
});
