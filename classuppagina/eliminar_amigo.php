<?php
session_start();
include 'conexion.php';

$usuario = $_SESSION['usuario'] ?? '';
$amigo = $_GET['amigo'] ?? '';

if (!$usuario || !$amigo) {
    die("Error: datos incompletos.");
}


$sql = "DELETE FROM amigos WHERE usuario = ? AND amigo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $usuario, $amigo);

if ($stmt->execute()) {
    header("Location: amigos.php?mensaje=eliminado");
    exit();
} else {
    echo "Error al eliminar amigo.";
}
?>
