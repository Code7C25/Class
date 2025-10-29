<?php
header('Content-Type: application/json');
include 'conexion.php'; // tu conexión a la base de datos

$texto = $_GET['q'] ?? '';
$usuarioActual = $_GET['usuarioActual'] ?? '';

// Preparar consulta segura
$sql = "SELECT usuario, fotoPerfil FROM users 
        WHERE usuario LIKE ? AND usuario != ?";
$stmt = $conn->prepare($sql);
$busqueda = "%$texto%";
$stmt->bind_param("ss", $busqueda, $usuarioActual);
$stmt->execute();
$result = $stmt->get_result();

$usuarios = [];
while($fila = $result->fetch_assoc()) {
    $usuarios[] = $fila;
}

echo json_encode($usuarios);
?>
