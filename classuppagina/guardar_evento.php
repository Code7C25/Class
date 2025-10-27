<?php
session_start();

// Datos de conexión
$host = "localhost";
$user = "root";
$pass = "";
$db = "paginaclassup";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Error de conexión"]));
}

$usuario = $_SESSION['usuario'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $usuario) {
    $titulo = trim($_POST['titulo'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($titulo && $fecha) {
        $stmt = $conn->prepare("INSERT INTO recordatorios (usuario, titulo, fecha, hora, descripcion) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $usuario, $titulo, $fecha, $hora, $descripcion);
        $stmt->execute();
        echo json_encode(["status" => "ok"]);
        exit;
    }
}

echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
