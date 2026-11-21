const CACHE = 'katakataapp-v1';
const PRECACHE = [
  '/',
  '/leaderboard',
  '/manifest.json',
];

self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE).then(c => c.addAll(PRECACHE)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', e => {
  // Network-first for API calls, cache-first for assets
  if (e.request.url.includes('/api/')) {
    e.respondWith(
      fetch(e.request).catch(() =>
        new Response(JSON.stringify({
          question: 'What is the capital city of Uganda?',
          options: ['Kampala', 'Entebbe', 'Jinja', 'Gulu'],
          answerIndex: 0,
          explanation: 'Kampala is the capital and largest city of Uganda.',
          explanation_lg: 'Kampala ye kibuga ekikulu ky\'Uganda.',
          source: 'offline'
        }), { headers: { 'Content-Type': 'application/json' } })
      )
    );
    return;
  }

  e.respondWith(
    caches.match(e.request).then(cached => {
      const network = fetch(e.request).then(res => {
        if (res.ok) {
          caches.open(CACHE).then(c => c.put(e.request, res.clone()));
        }
        return res;
      });
      return cached || network;
    })
  );
});
