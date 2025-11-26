<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buscar Usuarios - ClassUp</title>
  <link rel="stylesheet" href="css/busqueda.css">
</head>
<body>
  <div class="container">

    <!-- Header -->
    <header class="profile-section">
      <h1>🔍 Buscar Usuarios</h1>
      <div class="search-bar">
        <img src="lupa.png" alt="Lupa" class="search-icon">
        <input type="text" id="search-input" placeholder="Buscar por nombre de usuario...">
      </div>
    </header>

    <!-- Resultados -->
    <section class="resultados" id="lista-usuarios">
      <p class="empty-msg">Comienza a buscar usuarios...</p>
    </section>

  </div>

  <!-- Cuadro inferior fijo -->
  <div class="cuadro-opciones">
    <a href="inicio.php" class="boton-opcion">Inicio</a>
    <a href="busqueda.php" class="boton-opcion">Buscar</a>
    <a href="perfil.php" class="boton-opcion">Perfil</a>
    <a href="amigos.php" class="boton-opcion">Amigos</a>
    <a href="configuracion.html" class="boton-opcion">Ajustes</a>
  </div>

  <script>
    const searchInput = document.getElementById('search-input');
    const listaUsuarios = document.getElementById('lista-usuarios');

    // Función para obtener usuarios desde PHP
    async function fetchUsuarios(texto = '') {
      const response = await fetch(`buscarUsuarios.php?q=${encodeURIComponent(texto)}`);
      const data = await response.json();
      return data;
    }

    // Mostrar usuarios en pantalla
    async function renderUsuarios(texto = '') {
      const usuarios = await fetchUsuarios(texto);
      listaUsuarios.innerHTML = '';

      if(usuarios.length === 0){
        listaUsuarios.innerHTML = '<p class="empty-msg">No se encontraron usuarios.</p>';
        return;
      }

      usuarios.forEach(u => {
        const div = document.createElement('div');
        div.classList.add('usuario-card');

        const btnAgregar = `<button class="add-btn">Agregar</button>`;

        div.innerHTML = `
          <div class="usuario-info">
            <img src="${u.fotoPerfil || 'foto.jpg'}" alt="Foto de ${u.usuario}" class="usuario-foto">
            <div>
              <p class="usuario-nombre">@${u.usuario}</p>
            </div>
          </div>
          ${btnAgregar}
        `;
        listaUsuarios.appendChild(div);

        // Evento para agregar amigos
        div.querySelector('.add-btn').addEventListener('click', async () => {
          const formData = new FormData();
          formData.append('amigo', u.usuario);

          const res = await fetch('agregarAmigo.php', { method: 'POST', body: formData });
          const data = await res.json();

          if(data.success){
            alert(`${u.usuario} agregado como amigo`);
            renderUsuarios(searchInput.value);
          } else {
            alert('Error al agregar amigo');
          }
        });
      });
    }

    searchInput.addEventListener('input', () => renderUsuarios(searchInput.value));
    renderUsuarios(); // render inicial
  </script>
</body>
</html>
