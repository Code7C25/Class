<?php
include 'conexion.php';
session_start();

// --- Verifica login ---
if (!isset($_SESSION['usuario']) && !isset($_COOKIE['usuario'])) {
  header("Location: login.html");
  exit();
}

$usuario = $_SESSION['usuario'] ?? $_COOKIE['usuario'];
$fotoPerfil = $_SESSION['fotoPerfil'] ?? 'https://via.placeholder.com/100';

// --- Guardar nuevo recordatorio ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['titulo'], $_POST['fecha'])) {
  $titulo = trim($_POST['titulo']);
  $fecha = trim($_POST['fecha']);
  $hora = $_POST['hora'] ?? '';
  $descripcion = $_POST['descripcion'] ?? '';

  $stmt = $conn->prepare("INSERT INTO recordatorios (usuario, titulo, fecha, hora, descripcion) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $usuario, $titulo, $fecha, $hora, $descripcion);
  $stmt->execute();
  $stmt->close();

  // Redirige para evitar reenvío de formulario
  header("Location: inicio.php");
  exit();
}

// --- Mostrar recordatorios del usuario ---
$stmt = $conn->prepare("SELECT * FROM recordatorios WHERE usuario = ? ORDER BY fecha DESC, hora DESC");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$eventos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calendario Social</title>
  <link rel="stylesheet" href="css/inicio.css">
  <style>
    .campana-btn { background: none; border: none; font-size: 22px; cursor: pointer; margin-left: auto; transition: transform 0.2s; }
    .campana-btn:hover { transform: scale(1.2); }
    #calendarioContainer { display: none; margin-top: 15px; }
  </style>
</head>
<body>
  <header class="app-header">
    <div class="brand">
      <div class="app-logo" role="img" aria-label="ClassUp logo"></div>
      <h1 class="app-title">ClassUp</h1>
    </div>
  </header>

  <div class="container">
    <header class="profile-section">
      <div class="profile-card">
        <img id="foto-perfil-inicio" src="<?= htmlspecialchars($fotoPerfil) ?>" alt="fotoPerfil" class="profile-pic">
        <div class="profile-info">
          <h2>Mi Calendario Social</h2>
          <p>@<?= htmlspecialchars($usuario) ?></p>
        </div>
      </div>
    </header>

    <section class="calendar-section">
      <h3>Recordatorios recientes</h3>
      <button class="event-btn" onclick="toggleCalendario()">➕ Nuevo recordatorio</button>

      <!-- FORMULARIO oculto -->
      <div id="calendarioContainer">
        <form id="event-form" method="POST">
          <input type="text" name="titulo" placeholder="Título del evento" required>
          <input type="date" name="fecha" required>
          <input type="time" name="hora">
          <input type="text" name="descripcion" placeholder="Descripción (opcional)">
          <button type="submit">Guardar recordatorio</button>
        </form>
      </div>

      <!-- Recordatorios -->
      <div id="postsContainer">
        <?php if (empty($eventos)): ?>
          <p class="empty-msg">Todavía no hay recordatorios 📭</p>
        <?php else: ?>
          <?php foreach ($eventos as $ev): ?>
            <div class="post">
              <div class="post-header">
                <img src="<?= htmlspecialchars($fotoPerfil) ?>" class="avatar" alt="avatar">
                <div>
                  <strong><?= htmlspecialchars($ev['titulo']) ?></strong>
                  <p class="post-date">📅 <?= htmlspecialchars($ev['fecha']) ?> ⏰ <?= htmlspecialchars($ev['hora'] ?: 'Sin hora') ?></p>
                </div>
                <button class="campana-btn" onclick="alert('Recordatorio activado 🔔')">🔔</button>
              </div>
              <div class="post-content">
                <p><?= htmlspecialchars($ev['descripcion'] ?: 'Sin descripción') ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <div class="cuadro-opciones">
      <a href="inicio.php" class="boton-opcion">🏠 Inicio</a>
      <a href="busqueda.html" class="boton-opcion">🔍 Buscar</a>
      <a href="perfil.html" class="boton-opcion">👤 Perfil</a>
      <a href="amigos.html" class="boton-opcion">👥 Amigos</a>
      <a href="configuracion.html" class="boton-opcion">⚙️ Ajustes</a>
    </div>
  </div>

  <script>
    function toggleCalendario() {
      const calendario = document.getElementById("calendarioContainer");
      calendario.style.display = (calendario.style.display === "none" || calendario.style.display === "") ? "block" : "none";
    }
  </script>
</body>
</html>
