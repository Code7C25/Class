<?php
// login.php - procesa el formulario de login y crea sesión/cookie
include 'conexion.php';
session_start();

// Aceptar sólo métodos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$clave = isset($_POST['clave']) ? trim($_POST['clave']) : '';

if ($usuario === '' || $clave === '') {
    echo "<script>alert('Completa usuario y contraseña.'); window.location='login.html';</script>";
    exit;
}

// Crear tabla usuarios si no existe (campos mínimos: id, nombre, clave)
$createTableSql = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    clave VARCHAR(255) NOT NULL,
    creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conexion->query($createTableSql);

// Buscar usuario
$stmt = $conexion->prepare('SELECT id, nombre, clave FROM usuarios WHERE nombre = ? LIMIT 1');
$stmt->bind_param('s', $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    // Usuario existe -> verificar contraseña
    $row = $result->fetch_assoc();
    $stored = $row['clave'];
    // Para compatibilidad con el sistema existente, aquí hacemos una verificación simple.
    // Si más adelante querés seguridad, reemplazar por password_hash / password_verify.
    if ($stored === $clave) {
        // Login exitoso
        $_SESSION['usuario'] = $usuario;
        setcookie('usuario', $usuario, time() + 60*60*24*30, '/');
        header('Location: perfil.html');
        exit;
    } else {
        echo "<script>alert('Usuario o contraseña incorrectos.'); window.location='login.html';</script>";
        exit;
    }
} else {
    // Usuario no existe -> crear el usuario con la contraseña proporcionada
    $insert = $conexion->prepare('INSERT INTO usuarios (nombre, clave) VALUES (?, ?)');
    $insert->bind_param('ss', $usuario, $clave);
    if ($insert->execute()) {
        $_SESSION['usuario'] = $usuario;
        setcookie('usuario', $usuario, time() + 60*60*24*30, '/');
        header('Location: perfil.html');
        exit;
    } else {
        echo "<script>alert('Error al crear usuario. Intenta luego.'); window.location='login.html';</script>";
        exit;
    }
}
