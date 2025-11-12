self.addEventListener('install', () => {
  console.log('Service Worker instalado');
});

self.addEventListener('activate', () => {
  console.log('Service Worker activo');
});

self.addEventListener('push', e => {
  const data = e.data.json();
  self.registration.showNotification(data.title, {
    body: data.body,
    icon: data.icon,
    sound: 'https://cdn.pixabay.com/download/audio/2021/09/06/audio_0c385da3b7.mp3'
  });
});
