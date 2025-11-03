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

    header("Location: inicio.php");
    exit();
}

// --- Activar recordatorio ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['activar_id'])) {
    $activar_id = intval($_POST['activar_id']);

    $sql = "UPDATE recordatorios
            SET activado = 1
            WHERE id = ?
              AND (usuario = ?
                   OR usuario IN (SELECT amigo FROM amigos WHERE usuario = ?))";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error en la consulta SQL (activar recordatorio): " . $conn->error);
    }

    $stmt->bind_param("iss", $activar_id, $usuario, $usuario);
    $stmt->execute();
    $stmt->close();

    header("Location: inicio.php");
    exit();
}

// --- Mostrar recordatorios del usuario y amigos ---
$sql = "SELECT r.*, u.fotoPerfil 
        FROM recordatorios r
        JOIN users u ON r.usuario = u.usuario
        WHERE r.usuario = ? 
           OR r.usuario IN (SELECT amigo FROM amigos WHERE usuario = ?)
        ORDER BY r.fecha DESC, r.hora DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $usuario, $usuario);
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
  .comentarios { margin-top: 10px; border-top: 1px solid #ddd; padding-top: 8px; }
  .comentarios p { margin: 5px 0; }
  .comentarios form { display: flex; gap: 5px; margin-top: 5px; }
  .comentarios input[type="text"] { flex: 1; padding: 5px; border-radius: 6px; border: 1px solid #ccc; }
  .comentarios button { background: #fff8f0; border: none; padding: 5px 10px; border-radius: 6px; cursor: pointer; }
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
      <form id="event-form" method="POST" action="">
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
              <img src="<?= htmlspecialchars($ev['fotoPerfil'] ?? 'https://via.placeholder.com/100') ?>" class="avatar" alt="avatar">
              <div>
                <strong>@<?= htmlspecialchars($ev['usuario']) ?></strong>
                <p class="post-date"><?= htmlspecialchars($ev['titulo']) ?> 📅 <?= htmlspecialchars($ev['fecha']) ?> ⏰ <?= htmlspecialchars($ev['hora'] ?: 'Sin hora') ?></p>
              </div>
              <?php if (empty($ev['activado'])): ?>
                <form method="POST" style="margin-left:auto;">
                  <input type="hidden" name="activar_id" value="<?= htmlspecialchars($ev['id']) ?>">
                  <button class="campana-btn" type="submit">🔔</button>
                </form>
              <?php else: ?>
                <span style="margin-left:auto;">✅ Activado</span>
              <?php endif; ?>
            </div>
            <div class="post-content">
              <p><?= htmlspecialchars($ev['descripcion'] ?: 'Sin descripción') ?></p>
            </div>

            <!-- COMENTARIOS -->
            <div class="comentarios">
            <?php
            $cid = $ev['id'];

            $q_sql = "SELECT * FROM comentarios WHERE recordatorio_id = ? ORDER BY creado_en ASC";
            $q = $conn->prepare($q_sql);

            if ($q) {
                $q->bind_param("i", $cid);
                $q->execute();
                $rc = $q->get_result();
                while ($c = $rc->fetch_assoc()):
            ?>
                <p><strong>@<?= htmlspecialchars($c['usuario']) ?>:</strong> <?= htmlspecialchars($c['contenido']) ?></p>
            <?php
                endwhile;
                $q->close();
            } else {
                echo "<p style='color:red; font-size:12px;'>⚠️ Error SQL: " . htmlspecialchars($conn->error) . "</p>";
            }
            ?>
            <form method="POST" action="guardar_comentario.php">
              <input type="hidden" name="recordatorio_id" value="<?= htmlspecialchars($cid) ?>">
              <input type="text" name="contenido" placeholder="Escribe un comentario..." required>
              <button type="submit">💬</button>
            </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <div class="cuadro-opciones">
    <a href="inicio.php" class="boton-opcion">🏠 Inicio</a>
    <a href="busqueda.php" class="boton-opcion">🔍 Buscar</a>
    <a href="perfil.php" class="boton-opcion">👤 Perfil</a>
    <a href="amigos.php" class="boton-opcion">👥 Amigos</a>
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
