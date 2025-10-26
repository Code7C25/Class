<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
$usuario = $_SESSION['usuario'];
$fotoPerfil = $_SESSION['fotoPerfil'] ?? 'default.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil | ClassUp</title>
</head>
<body>
    <h1>Bienvenido, <?= htmlspecialchars($usuario) ?></h1>
    <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto de perfil" width="150">
    <p><a href="logout.php">Cerrar sesión</a></p>
</body>
</html>
