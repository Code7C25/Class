<?php
session_start();

// Conexión a MySQL
$host = "localhost";
$user = "root";
$pass = "";
$db = "paginaclassup";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $clave = trim($_POST['clave']);

    if ($usuario === "" || $clave === "") {
        $error = "Completa todos los campos.";
    } else {
        // Buscar usuario en la base de datos
        $sql = "SELECT password, fotoPerfil FROM users WHERE usuario = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Error en la consulta SQL: " . $conn->error);
        }

        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($passwordHash, $fotoPerfil);
            $stmt->fetch();

            // Verificar contraseña
            if (password_verify($clave, $passwordHash)) {
                // Guardar datos en la sesión
                $_SESSION['usuario'] = $usuario;
                $_SESSION['fotoPerfil'] = $fotoPerfil;

                // Redirigir a inicio.php
                header("Location: inicio.php");
                exit;
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión | ClassUp</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>

<div class="container">
    <div class="logo">
        <img src="ClassUp.png" alt="ClassUp Logo" class="logo-img">
        <h1>Bienvenido a <span>ClassUp</span></h1>
    </div>

    <div class="form-card">
        <h2>Iniciar Sesión</h2>
        <form method="post" action="login.php" id="loginForm">
            <input type="text" id="usuario" name="usuario" placeholder="Nombre de usuario" required>
            <input type="password" id="clave" name="clave" placeholder="Contraseña" required>
            <button type="submit">Entrar</button>
        </form>
        <?php
        if (isset($error)) {
            echo "<p style='color:red; margin-top:10px;'>$error</p>";
        }
        ?>
        <p class="registrate">¿No tienes cuenta? 
            <a href="registro.html">Regístrate</a>
        </p>
    </div>
</div>

</body>
</html>
