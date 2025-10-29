<?php
session_start();
include 'conexion.php';

$usuario = $_SESSION['usuario'] ?? '';
$amigo = $_POST['amigo'] ?? '';

if (!$usuario || !$amigo) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

// Insertar amigo si no existe
$sql = "INSERT IGNORE INTO amigos (usuario, amigo) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $usuario, $amigo);
$stmt->execute();

echo json_encode(['success' => true]);
?>
