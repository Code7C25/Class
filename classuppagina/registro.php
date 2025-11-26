<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "paginaclassup");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario = trim($_POST["usuario"]);
    $password = trim($_POST["password"]);

    // Encriptar contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Procesar foto de perfil
    $fotoNombre = "default.png"; 
    if (!empty($_FILES["foto"]["name"])) {
        $fotoNombre = time() . "_" . basename($_FILES["foto"]["name"]);
        $rutaDestino = "uploads/" . $fotoNombre;
        move_uploaded_file($_FILES["foto"]["tmp_name"], $rutaDestino);
    }

    
    $sql = "INSERT INTO users (usuario, password, fotoPerfil) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $fotoRutaBD = "uploads/" . $fotoNombre; // Lo que se guarda en BD
    $stmt->bind_param("sss", $usuario, $passwordHash, $fotoRutaBD);

    if ($stmt->execute()) {

        $_SESSION["usuario"] = $usuario;
        $_SESSION["id"] = $stmt->insert_id;
        $_SESSION["fotoPerfil"] = $fotoRutaBD; 

        // REDIRIGIR AL PERFIL
        header("Location: perfil.php");
        exit();

    } else {
        echo "Error al registrar: " . $conexion->error;
    }

    $stmt->close();
}

$conexion->close();
?>
