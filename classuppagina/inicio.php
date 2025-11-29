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
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['titulo'], $_POST['fecha']) && empty($_POST['editar_id'])) {
    $titulo = trim($_POST['titulo']);
    $fecha = trim($_POST['fecha']);
    $hora = $_POST['hora'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $dias_antes = intval($_POST['dias_antes'] ?? 1);
    $hora_aviso = $_POST['hora_aviso'] ?? '08:00';

    $stmt = $conn->prepare("INSERT INTO recordatorios (usuario, titulo, fecha, hora, descripcion, dias_antes, hora_aviso) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssis", $usuario, $titulo, $fecha, $hora, $descripcion, $dias_antes, $hora_aviso);
    $stmt->execute();
    $stmt->close();

    header("Location: inicio.php");
    exit();
}

// --- EDITAR recordatorio existente ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['editar_id'])) {
    $editar_id = intval($_POST['editar_id']);
    $titulo = trim($_POST['titulo']);
    $fecha = trim($_POST['fecha']);
    $hora = $_POST['hora'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $dias_antes = intval($_POST['dias_antes'] ?? 1);
    $hora_aviso = $_POST['hora_aviso'] ?? '08:00';

    $sql = "UPDATE recordatorios 
        SET titulo=?, fecha=?, hora=?, descripcion=?, dias_antes=?, hora_aviso=?, notificado=0
        WHERE id=? AND usuario=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssisss", $titulo, $fecha, $hora, $descripcion, $dias_antes, $hora_aviso, $editar_id, $usuario);
    $stmt->execute();
    $stmt->close();

    header("Location: inicio.php?edit_ok=1");
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

// --- BORRAR recordatorio ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['borrar_id'])) {
    $borrar_id = intval($_POST['borrar_id']);

    $sql = "DELETE FROM recordatorios WHERE id = ? AND usuario = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error en la consulta SQL (borrar recordatorio): " . $conn->error);
    }

    $stmt->bind_param("is", $borrar_id, $usuario);
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

  /* Comentarios */
  .comentarios { margin-top: 10px; border-top: 1px solid #ddd; padding-top: 8px; }
  .comentario-item { display: flex; align-items: center; gap: 8px; margin: 5px 0; }
  .comentario-foto { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; border: 1px solid #b59b83; }
  .comentarios p { margin: 0; }
  .comentarios form { display: flex; gap: 5px; margin-top: 5px; }
  .comentarios input[type="text"] { flex: 1; padding: 5px; border-radius: 6px; border: 1px solid #ccc; }
  .comentarios button { background: #fff8f0; border: none; padding: 5px 10px; border-radius: 6px; cursor: pointer; }

  /* Botón borrar */
  .delete-btn { background: none; border: none; color: #d9534f; font-size: 20px; cursor: pointer; margin-left: 10px; }
  .delete-btn:hover { transform: scale(1.1); color: #b52b27; }

  /* Botón editar */
  .edit-btn {
    background: none;
    border: none;
    color: #8b5e34;
    font-size: 18px;
    cursor: pointer;
    margin-left: 10px;
  }
  .edit-btn:hover {
    color: #5c3a1d;
    transform: scale(1.1);
  }
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
        <h2>Calendario Escolar</h2>
        <p>@<?= htmlspecialchars($usuario) ?></p>
      </div>
    </div>
  </header>

  <section class="calendar-section">
    <button class="event-btn" onclick="toggleCalendario()">➕ Nuevo recordatorio</button>
    <h3>Recordatorios recientes</h3>

    <div id="calendarioContainer">
      <form id="event-form" method="POST" action="">
        <input type="text" name="titulo" placeholder="Título del evento" required>
        <input type="date" name="fecha" required>
        <input type="time" name="hora">
        <input type="text" name="descripcion" placeholder="Descripción (opcional)">
        <!-- NUEVO: selección de aviso anticipado -->
        <label>📅 Avisar cuántos días antes:</label>
        <select name="dias_antes">
          <option value="1">1 día antes</option>
          <option value="2">2 días antes</option>
          <option value="3">3 días antes</option>
        </select>
        <label>⏰ A qué hora avisar:</label>
        <input type="time" name="hora_aviso" value="08:00">
        <button type="submit">Guardar recordatorio</button>
      </form>
    </div>

    <div id="postsContainer">
      <?php if (empty($eventos)): ?>
        <p class="empty-msg">Todavía no hay recordatorios </p>
      <?php else: ?>
        <?php foreach ($eventos as $ev): ?>
          <div class="post">
            <div class="post-header">
              <img src="<?= htmlspecialchars($ev['fotoPerfil'] ?? 'https://via.placeholder.com/100') ?>" class="avatar" alt="avatar">
              <div>
                <strong>@<?= htmlspecialchars($ev['usuario']) ?></strong>
                <p class="post-date"><?= htmlspecialchars($ev['titulo']) ?> 📅 <?= htmlspecialchars($ev['fecha']) ?> ⏰ <?= htmlspecialchars($ev['hora'] ?: 'Sin hora') ?></p>
              </div>

              <?php if ($ev['usuario'] === $usuario): ?>
                <!-- BOTÓN EDITAR -->
                <a href="editar_recordatorio.php?id=<?= htmlspecialchars($ev['id']) ?>" class="edit-btn">✏️ Editar</a>

                <form method="POST" style="display:inline;">
                  <input type="hidden" name="borrar_id" value="<?= htmlspecialchars($ev['id']) ?>">
                  <button class="delete-btn" type="submit" onclick="return confirm('¿Seguro que quieres borrar este recordatorio?')">Eliminar</button>
                </form>
              <?php endif; ?>

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

            $q_sql = "SELECT c.contenido, c.usuario, u.fotoPerfil 
                      FROM comentarios c
                      JOIN users u ON c.usuario = u.usuario
                      WHERE c.recordatorio_id = ?
                      ORDER BY c.creado_en ASC";
            $q = $conn->prepare($q_sql);

            if ($q) {
                $q->bind_param("i", $cid);
                $q->execute();
                $rc = $q->get_result();
                while ($c = $rc->fetch_assoc()):
            ?>
                <div class="comentario-item">
                    <img src="<?= htmlspecialchars($c['fotoPerfil'] ?? 'https://via.placeholder.com/40') ?>" class="comentario-foto" alt="avatar">
                    <p><strong>
    <a href="perfil_amigo.php?usuario=<?= urlencode($c['usuario']) ?>" class="coment-user">
      @<?= htmlspecialchars($c['usuario']) ?>
    </a>:
  </strong>
  <?= htmlspecialchars($c['contenido']) ?></p>
                </div>
            <?php
                endwhile;
                $q->close();
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
    <a href="inicio.php" class="boton-opcion">
    <img src="img/inicio.jpg" class="icono-menu"></a>
    <a href="busqueda.php" class="boton-opcion">
    <img src="img/buscar.png" class="icono-menu"></a>
    <a href="perfil.php" class="boton-opcion">
    <img src="img/perfil.jpg" class="icono-menu"></a>
    <a href="amigos.php" class="boton-opcion">
    <img src="img/amigos.jpg" class="icono-menu"></a>
    <a href="configuracion.html" class="boton-opcion">
    <img src="img/ajuste.jpg" class="icono-menu"></a>
  </div>
</div>

<audio id="notifSound" src="https://cdn.pixabay.com/download/audio/2021/09/06/audio_0c385da3b7.mp3" preload="auto"></audio>

<script>
function toggleCalendario() {
    const calendario = document.getElementById("calendarioContainer");
    calendario.style.display = (calendario.style.display === "none" || calendario.style.display === "") ? "block" : "none";
}

// --- Notificaciones automáticas ---
setInterval(checkReminders, 60000); // cada 1 min

function checkReminders(){
  fetch('verificar_recordatorios.php')
    .then(r=>r.json())
    .then(data=>{
      if(data.length>0){
        const sound=document.getElementById("notifSound");
        sound.play();
        data.forEach(r=>{
          new Notification("⏰ "+r.titulo,{
            body:"📅 "+r.fecha+" "+(r.hora||"")+" → "+(r.descripcion||""),
            icon:"https://cdn-icons-png.flaticon.com/512/3898/3898080.png"
          });
        });
      }
    });
}

if(Notification.permission!=="granted") Notification.requestPermission();
</script>
</body>
</html>
