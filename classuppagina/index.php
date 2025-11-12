<?php

// index.php - simple login that uses conexion.php
// - crea la tabla `usuarios` si no existe
// - inserta el usuario si no existe
// - inicia sesión y pone una cookie 'usuario' para que el JS del front pueda usarla
include 'conexion.php';
include('notificaciones.php'); 
session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    if ($nombre === '') {
        $error = 'Por favor ingresa un nombre de usuario.';
    } else {
        // Asegurarse de que la tabla exista
        $sqlCreate = "CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conexion->query($sqlCreate);

        // Buscar usuario
        $stmt = $conexion->prepare('SELECT id FROM usuarios WHERE username = ?');
        $stmt->bind_param('s', $nombre);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $stmt->close();
            $ins = $conexion->prepare('INSERT INTO usuarios (username) VALUES (?)');
            $ins->bind_param('s', $nombre);
            $ins->execute();
            $ins->close();
        } else {
            $stmt->close();
        }

        // Guardar en sesión y cookie (para compatibilidad con front)
        $_SESSION['usuario'] = $nombre;
        setcookie('usuario', $nombre, time() + 3600, '/');

        // Redirigir al perfil (la app frontend usa perfil.html)
        header('Location: perfil.html');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - ClassUp</title>
  <link rel="stylesheet" href="css/index.css">
</head>
<body>
  <h1>Bienvenido a ClassUp</h1>
  <?php if ($error): ?>
    <p style="color:red"><?=htmlspecialchars($error)?></p>
  <?php endif; ?>
  <form method="post" action="index.php" class="form-container">
    <input type="text" name="nombre" placeholder="Ingresa tu usuario" required>
    <button type="submit">Entrar</button>
    <a href="registro.html" class="link-registrate">Registrate</a>
  </form>
</body>
</html>
