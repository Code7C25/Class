<?php
session_start();

// Datos de conexión a MySQL
$host = "localhost";
$user = "root";
$pass = "";
$db = "classup";

// Conectar a la base de datos
$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Crear carpeta uploads si no existe
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    $fotoPerfil = null;

    // Encriptar contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Subir foto si hay
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $fotoPerfil = "uploads/" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $fotoPerfil);
    }

    // Insertar usuario en la tabla "users"
    $stmt = $conn->prepare("INSERT INTO users (usuario, password, fotoPerfil) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $usuario, $passwordHash, $fotoPerfil);

    if ($stmt->execute()) {
        // Guardar usuario y foto en localStorage y redirigir a perfil.html
        echo "<script>
            localStorage.setItem('usuario', '".addslashes($usuario)."');
            localStorage.setItem('fotoPerfil', '".addslashes($fotoPerfil)."');
            window.location.href = 'perfil.html';
        </script>";
        exit;
    } else {
        $error = "El nombre de usuario ya existe o hubo un error.";
        echo "<p style='color:red;'>$error</p>";
    }
}
?>
