<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db = "paginaclassup";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode([]));
}

$usuario = $_SESSION['usuario'] ?? null;
if (!$usuario) {
    echo json_encode([]);
    exit;
}

$result = $conn->prepare("SELECT titulo, fecha, hora, descripcion FROM recordatorios WHERE usuario = ? ORDER BY fecha DESC");
$result->bind_param("s", $usuario);
$result->execute();
$res = $result->get_result();

$eventos = [];
while ($row = $res->fetch_assoc()) {
    $eventos[] = $row;
}

echo json_encode($eventos);
