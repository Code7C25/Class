<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['usuario']) && !isset($_COOKIE['usuario'])) {
    header("Location: login.html");
    exit();
}

$usuario = $_SESSION['usuario'] ?? $_COOKIE['usuario'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['recordatorio_id'], $_POST['contenido'])) {
    $recordatorio_id = intval($_POST['recordatorio_id']);
    $contenido = trim($_POST['contenido']);

    if ($contenido !== '') {
        $stmt = $conn->prepare("INSERT INTO comentarios (recordatorio_id, usuario, contenido, creado_en) VALUES (?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("iss", $recordatorio_id, $usuario, $contenido);
            $stmt->execute();
            $stmt->close();
        } else {
            die("Error al insertar comentario: " . $conn->error);
        }
    }
}

// Redirige de vuelta a la página donde estaba el comentario
header("Location: inicio.php");
exit();
