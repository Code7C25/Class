<?php
header('Content-Type: application/json');
$conexion = new mysqli("localhost", "root", "", "nombre_de_tu_base");

// Verificar conexión
if ($conexion->connect_error) {
    die(json_encode([]));
}

$texto = isset($_GET['q']) ? $conexion->real_escape_string($_GET['q']) : '';

// Buscar usuarios que coincidan con el texto (excepto el usuario actual)
$usuarioActual = isset($_GET['usuarioActual']) ? $_GET['usuarioActual'] : '';
$sql = "SELECT nombre, usuario, foto FROM usuarios 
        WHERE (nombre LIKE '%$texto%' OR usuario LIKE '%$texto%') 
        AND usuario != '$usuarioActual'";

$resultado = $conexion->query($sql);
$usuarios = [];

if ($resultado) {
    while($fila = $resultado->fetch_assoc()) {
        $usuarios[] = $fila;
    }
}

echo json_encode($usuarios);
?>
