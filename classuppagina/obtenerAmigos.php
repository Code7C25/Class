<?php
session_start();
include 'conexion.php';

$usuario = $_SESSION['usuario'] ?? '';
if(!$usuario){
    echo json_encode([]);
    exit;
}

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

echo json_encode($amigos);
?>
