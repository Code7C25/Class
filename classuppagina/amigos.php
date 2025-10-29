<?php
session_start();
include 'conexion.php';

$usuario = $_SESSION['usuario'] ?? '';
if(!$usuario){
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
while($fila = $result->fetch_assoc()){
    $amigos[] = $fila;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mis Amigos - ClassUp</title>
  <link rel="stylesheet" href="css/amigos.css">
</head>
<body>
  <div class="container">
    <header class="profile-section">
      <h1>👥 Mis Amigos</h1>
    </header>

    <section id="lista-amigos">
      <?php if(count($amigos) === 0): ?>
        <p class="empty-msg">Aún no tienes amigos agregados.</p>
      <?php else: ?>
        <?php foreach($amigos as $a): ?>
          <div class="usuario-card">
            <div class="usuario-info">
              <img src="<?= htmlspecialchars($a['fotoPerfil']) ?>" class="amigo-foto" alt="Foto de <?= htmlspecialchars($a['usuario']) ?>">
              <div>
                <p class="usuario-nombre">@<?= htmlspecialchars($a['usuario']) ?></p>
              </div>
            </div>

            <!-- 🔹 Botón que lleva al perfil del amigo -->
            <button class="ver-perfil-btn" 
              onclick="window.location.href='perfil_amigo.php?usuario=<?= urlencode($a['usuario']) ?>'">
              👀 Ver perfil
            </button>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

    <div class="cuadro-opciones">
      <a href="inicio.php" class="boton-opcion">🏠 Inicio</a>
      <a href="busqueda.php" class="boton-opcion">🔍 Buscar</a>
      <a href="perfil.html" class="boton-opcion">👤 Perfil</a>
      <a href="amigos.php" class="boton-opcion">👥 Amigos</a>
      <a href="configuracion.html" class="boton-opcion">⚙️ Ajustes</a>
    </div>
  </div>
</body>
</html>
