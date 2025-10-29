<?php
session_start();
include 'conexion.php';

$usuario = $_SESSION['usuario'] ?? '';

if (!$usuario) {
    header("Location: inicio.html");
    exit;
}

// Obtener datos actuales del usuario
$sql = "SELECT usuario, fotoPerfil FROM users WHERE usuario=?";
if (!$stmt = $conn->prepare($sql)) {
    die("Error en SELECT: " . $conn->error);
}
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$datos = $result->fetch_assoc();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoUsuario = $_POST['usuario'];

    // Manejo de foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $nombreArchivo = time() . '_' . basename($_FILES['foto']['name']);
        $rutaFoto = 'uploads/' . $nombreArchivo;
        move_uploaded_file($_FILES['foto']['tmp_name'], $rutaFoto);
    } else {
        $rutaFoto = $datos['fotoPerfil'];
    }

    // Actualizar en la base de datos
    $sqlUpdate = "UPDATE users SET usuario=?, fotoPerfil=? WHERE usuario=?";
    if (!$stmtUpdate = $conn->prepare($sqlUpdate)) {
        die("Error en UPDATE: " . $conn->error);
    }
    $stmtUpdate->bind_param("sss", $nuevoUsuario, $rutaFoto, $usuario);
    $stmtUpdate->execute();

    // Actualizar sesión y localStorage
    $_SESSION['usuario'] = $nuevoUsuario;
    echo "<script>
        localStorage.setItem('usuario', '" . addslashes($nuevoUsuario) . "');
        localStorage.setItem('fotoPerfil', '" . addslashes($rutaFoto) . "');
        alert('Perfil actualizado correctamente');
        window.location='perfil.html';
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil - ClassUp</title>
    <link rel="stylesheet" href="css/perfil.css">
</head>
<body>
<div class="container">
    <div class="profile-section">
        <div class="profile-card">
            <img src="<?= htmlspecialchars($datos['fotoPerfil'] ?: 'foto.jpg') ?>" alt="Foto actual" class="profile-pic">
            <div class="profile-info">
                <h2>@<?= htmlspecialchars($usuario) ?></h2>
                <p>Editar perfil</p>
            </div>
        </div>

        <form method="post" enctype="multipart/form-data" style="margin-top:20px;">
            <label for="usuario">Nuevo nombre de usuario:</label><br>
            <input type="text" id="usuario" name="usuario" value="<?= htmlspecialchars($datos['usuario']) ?>" required style="margin-bottom:10px; padding:6px; border-radius:6px; border:1px solid #b59b83;"><br>

            <label for="foto">Nueva foto de perfil:</label><br>
            <input type="file" name="foto" id="foto" accept="image/*" style="margin-bottom:10px;"><br>

            <button type="submit" class="boton-opcion">💾 Guardar cambios</button>
            <a href="perfil.html" class="boton-opcion" style="background:#b59b83;">↩ Volver</a>
        </form>
    </div>
</div>
</body>
</html>
