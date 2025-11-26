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

// --- Verificar qué amigo se quiere ver ---
$amigo = $_GET['usuario'] ?? '';
if (!$amigo) {
    echo "No se seleccionó ningún amigo.";
    exit();
}


// --- Traer datos del amigo ---
$stmtUser = $conn->prepare("SELECT usuario, fotoPerfil FROM users WHERE usuario=?");
$stmtUser->bind_param("s", $amigo);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$datosAmigo = $resultUser->fetch_assoc();
$stmtUser->close();

// --- Traer recordatorios del amigo ---
$sql = "SELECT r.*, u.fotoPerfil 
        FROM recordatorios r
        JOIN users u ON r.usuario = u.usuario
        WHERE r.usuario = ?
        ORDER BY r.fecha DESC, r.hora DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $amigo);
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
<title>Perfil de <?= htmlspecialchars($amigo) ?> - ClassUp</title>
<link rel="stylesheet" href="css/inicio.css">
<style>
  .campana-btn { display:none; } /* No se pueden activar recordatorios del amigo */
  #calendarioContainer { display: none; }
  .comentarios { margin-top: 10px; border-top: 1px solid #ddd; padding-top: 8px; }
  .comentario-item { display: flex; align-items: center; gap: 8px; margin: 5px 0; }
  .comentario-foto { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; border: 1px solid #b59b83; }
  .comentarios p { margin: 0; }
</style>
</head>
<body>
<a href="inicio.php" class="btn-volver">⬅</a>
<header class="app-header">
  <div class="brand">
    <div class="app-logo" role="img" aria-label="ClassUp logo"></div>
    <h1 class="app-title">ClassUp</h1>
  </div>
</header>

<div class="container">
  <header class="profile-section">
    <div class="profile-card">
      <img id="foto-perfil-inicio" src="<?= htmlspecialchars($datosAmigo['fotoPerfil'] ?? 'https://via.placeholder.com/100') ?>" alt="fotoPerfil" class="profile-pic">
      <div class="profile-info">
        <h2>@<?= htmlspecialchars($datosAmigo['usuario']) ?></h2>
        <p>Perfil de amigo</p>
      </div>
    </div>
  </header>

  <section class="calendar-section">
    <h3>Recordatorios de <?= htmlspecialchars($datosAmigo['usuario']) ?></h3>

    <div id="postsContainer">
      <?php if (empty($eventos)): ?>
        <p class="empty-msg">Este amigo no tiene recordatorios 📭</p>
      <?php else: ?>
        <?php foreach ($eventos as $ev): ?>
          <div class="post">
            <div class="post-header">
              <img src="<?= htmlspecialchars($ev['fotoPerfil'] ?? 'https://via.placeholder.com/100') ?>" class="avatar" alt="avatar">
              <div>
                <strong>@<?= htmlspecialchars($ev['usuario']) ?></strong>
                <p class="post-date"><?= htmlspecialchars($ev['titulo']) ?> 📅 <?= htmlspecialchars($ev['fecha']) ?> ⏰ <?= htmlspecialchars($ev['hora'] ?: 'Sin hora') ?></p>
              </div>
            </div>
            <div class="post-content">
              <p><?= htmlspecialchars($ev['descripcion'] ?: 'Sin descripción') ?></p>
            </div>

            <!-- COMENTARIOS -->
            <div class="comentarios">
            <?php
            $cid = $ev['id'];

            // Traer comentarios con foto de perfil
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
                    <p><strong>@<?= htmlspecialchars($c['usuario']) ?>:</strong> <?= htmlspecialchars($c['contenido']) ?></p>
                </div>
            <?php
                endwhile;
                $q->close();
            } else {
                echo "<p style='color:red; font-size:12px;'>⚠️ Error SQL: " . htmlspecialchars($conn->error) . "</p>";
            }
            ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <div class="cuadro-opciones">
    <a href="inicio.php" class="boton-opcion">Inicio</a>
    <a href="busqueda.php" class="boton-opcion">Buscar</a>
    <a href="perfil.php" class="boton-opcion">Perfil</a>
    <a href="amigos.php" class="boton-opcion">Amigos</a>
    <a href="configuracion.html" class="boton-opcion">Ajustes</a>
  </div>
</div>
</body>
</html>
