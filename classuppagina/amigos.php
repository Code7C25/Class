<?php
session_start();
include 'conexion.php';

$usuario = $_SESSION['usuario'] ?? '';
if (!$usuario) {
    die("Debes iniciar sesión.");
}

// Obtener amigos desde DB
$sql = "SELECT u.usuario, u.fotoPerfil
        FROM amigos a
        JOIN users u ON a.amigo = u.usuario
        WHERE a.usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$amigos = [];
while ($fila = $result->fetch_assoc()) {
    $amigos[] = $fila;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mis Amigos - ClassUp</title>
  <link rel="stylesheet" href="css/amigos.css?v=2">

</head>
<body>
  <div class="container">
    
    <!-- Encabezado -->
    <header class="profile-section">
      <h1>👥 Mis Amigos</h1>
    </header>

    <!-- Lista de amigos -->
    <section id="lista-amigos">
      <?php if (empty($amigos)): ?>
        <p class="empty-msg">Aún no tienes amigos agregados.</p>
      <?php else: ?>
        <?php foreach ($amigos as $a): ?>
          <div class="usuario-card">
            <div class="usuario-info">
              <img 
                src="<?= htmlspecialchars($a['fotoPerfil']) ?>" 
                class="amigo-foto" 
                alt="Foto de <?= htmlspecialchars($a['usuario']) ?>"
              >
              <p class="usuario-nombre">@<?= htmlspecialchars($a['usuario']) ?></p>
            </div>

            <button 
              class="ver-perfil-btn" 
              onclick="window.location.href='perfil_amigo.php?usuario=<?= urlencode($a['usuario']) ?>'">
              👀 Ver perfil
            </button>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

    <!-- Menú inferior -->
    <nav class="cuadro-opciones">
      <a href="inicio.php" class="boton-opcion">🏠 Inicio</a>
      <a href="busqueda.php" class="boton-opcion">🔍 Buscar</a>
      <a href="perfil.php" class="boton-opcion">👤 Perfil</a>
      <a href="amigos.php" class="boton-opcion active">👥 Amigos</a>
      <a href="configuracion.php" class="boton-opcion">⚙️ Ajustes</a>
    </nav>

  </div>
</body>
</html>
