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

    if (empty($usuario) || empty($password)) {
        echo "<p style='color:red;'>Completa todos los campos.</p>";
        exit;
    }

    // Encriptar contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Subir foto si hay
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $fotoPerfil = "uploads/" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $fotoPerfil);
    }

    // Verificar si el usuario ya existe
    $check = $conn->prepare("SELECT id FROM users WHERE usuario = ?");
    $check->bind_param("s", $usuario);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<p style='color:red;'>El nombre de usuario ya existe.</p>";
        exit;
    }

    // Insertar usuario en la tabla
    $stmt = $conn->prepare("INSERT INTO users (usuario, password, fotoPerfil) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $usuario, $passwordHash, $fotoPerfil);

    if ($stmt->execute()) {
        echo "<script>
            localStorage.setItem('usuario', '".addslashes($usuario)."');
            localStorage.setItem('fotoPerfil', '".addslashes($fotoPerfil)."');
            window.location.href = 'perfil.html';
        </script>";
        exit;
    } else {
        echo "<p style='color:red;'>Error al registrar usuario: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $check->close();
}

$conn->close();
?>
