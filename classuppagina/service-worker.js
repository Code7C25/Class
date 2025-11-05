const CACHE_NAME = 'classup-cache-v1';
const urlsToCache = [
  'index.html',
  'perfil.html',
  'registro.html',
  'calendario.html',
  'cerrasesion.html',
  'configuracion.html',
  'inicio.html',
  'login.html',
  'css/index.css',
  'css/amigos.css',
  'css/login.css',
  'css/perfil.css',
  'css/registro.css',
  'manifest.json',
  'images/icon-192.png',
  'images/icon-512.png',
  'css/busqueda.css',
  'css/inicio.css',
  'main.js',
  'css/inicio.css',
  'css/mispublicaciones.css',
  'css/perfil_amigo.css',
  'css/registro.css',


  // agregá más archivos esenciales si querés
];

// Instalación: cache inicial
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(urlsToCache.map(url => new Request(url, {mode: 'no-cors'})))
        .catch(err => console.warn('Algunos archivos no pudieron ser cacheados:', err));
    })
  );
  self.skipWaiting();
});

// Activación
self.addEventListener('activate', event => {
  event.waitUntil(clients.claim());
});

// Fetch: primero cache, si no hay, fetch normal
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => response || fetch(event.request).then(fetchRes => {
        return caches.open(CACHE_NAME).then(cache => {
          // Guardar en cache dinámicamente
          cache.put(event.request, fetchRes.clone());
          return fetchRes;
        });
      }).catch(() => {
        // Opcional: fallback si falla la conexión
        return caches.match('index.html');
      }))
  );
});
