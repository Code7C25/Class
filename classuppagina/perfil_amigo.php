<?php
session_start();
include 'conexion.php';

if (!isset($_GET['usuario'])) {
    die("Error: falta el parámetro 'usuario'.");
}

$amigoUsuario = $_GET['usuario'];

// Verificar conexión
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

/* ========== Obtener datos del amigo (solo columnas que existen en tu tabla users) ========== */
$sql = "SELECT usuario, fotoPerfil FROM users WHERE usuario = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error al preparar la consulta de usuario: " . $conn->error);
}
$stmt->bind_param("s", $amigoUsuario);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Amigo no encontrado.");
}
$amigo = $result->fetch_assoc();

/* ========== Intentar obtener recordatorios con columnas esperadas ========== */
$recordatorios = [];
$sqlRec = "SELECT titulo, descripcion, fecha, hora FROM recordatorios WHERE usuario = ? ORDER BY fecha DESC, hora DESC";
$stmtRec = $conn->prepare($sqlRec);

if ($stmtRec) {
    // Consulta estándar funcionó
    $stmtRec->bind_param("s", $amigoUsuario);
    $stmtRec->execute();
    $resRec = $stmtRec->get_result();
    while ($r = $resRec->fetch_assoc()) {
        $recordatorios[] = $r;
    }
} else {
    // Fallback: la tabla recordatorios o las columnas no coinciden.
    // Intentamos recuperar todas las columnas disponibles para ese usuario.
    $sqlRec2 = "SELECT * FROM recordatorios WHERE usuario = ? ORDER BY id DESC";
    $stmtRec2 = $conn->prepare($sqlRec2);
    if ($stmtRec2) {
        $stmtRec2->bind_param("s", $amigoUsuario);
        $stmtRec2->execute();
        $resRec2 = $stmtRec2->get_result();
        while ($r = $resRec2->fetch_assoc()) {
            $recordatorios[] = $r;
        }
    } else {
        // No existe tabla recordatorios o error grave: lo registramos y seguimos (se mostrará mensaje).
        error_log("Error preparar recordatorios: " . $conn->error);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil de <?= htmlspecialchars($amigo['usuario']) ?> - ClassUp</title>
  <link rel="stylesheet" href="css/perfil_amigo.css">
</head>
<body>
  <header class="app-header">
    <h1>Perfil de <?= htmlspecialchars($amigo['usuario']) ?></h1>
  </header>

  <main class="perfil-container">
    <img 
      src="<?= htmlspecialchars($amigo['fotoPerfil'] ?: 'https://via.placeholder.com/120') ?>" 
      class="foto-perfil" 
      alt="Foto de <?= htmlspecialchars($amigo['usuario']) ?>"
    >

    <h2>@<?= htmlspecialchars($amigo['usuario']) ?></h2>

    <div class="info-extra">
      <h3>📅 Recordatorios</h3>

      <?php if (count($recordatorios) === 0): ?>
        <p class="empty-msg">Este usuario no tiene recordatorios públicos.</p>
      <?php else: ?>
        <?php foreach ($recordatorios as $rec): ?>
          <div class="recordatorio-card">
            <?php
            // Si la fila tiene 'titulo' y 'descripcion' (estructura esperada), mostramos formateado
            if (isset($rec['titulo']) || isset($rec['descripcion']) || isset($rec['fecha']) || isset($rec['hora'])) {
                $titulo = $rec['titulo'] ?? '(sin título)';
                $descripcion = $rec['descripcion'] ?? '';
                $fecha = $rec['fecha'] ?? '';
                $hora = $rec['hora'] ?? '';
                echo "<h4>" . htmlspecialchars($titulo) . "</h4>";
                if ($descripcion !== '') echo "<p>" . nl2br(htmlspecialchars($descripcion)) . "</p>";
                if ($fecha || $hora) echo "<small>📅 " . htmlspecialchars(trim("$fecha $hora")) . "</small>";
            } else {
                // Fallback: la fila tiene otros campos; listamos clave:valor
                echo "<ul>";
                foreach ($rec as $k => $v) {
                    echo "<li><strong>" . htmlspecialchars($k) . ":</strong> " . htmlspecialchars($v) . "</li>";
                }
                echo "</ul>";
            }
            ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <button class="volver-btn" onclick="window.location.href='amigos.php'">⬅️ Volver</button>
  </main>
</body>
</html>
