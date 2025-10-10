<?php
// Datos de conexión
$host = "localhost";
$usuario = "root";
$clave = ""; // Si usás XAMPP, dejar vacío. Si usás otro, poner la contraseña.
$bd = "classup";

// Crear conexión
$conexion = new mysqli($host, $usuario, $clave, $bd);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
} else {
    // Opcional: mostrar mensaje de éxito (solo para pruebas)
    // echo "Conexión exitosa a la base de datos";
}
?>
