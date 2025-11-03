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
    $nuevoUsuario = trim($_POST['usuario']);

    // Manejo de foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $nombreArchivo = time() . '_' . basename($_FILES['foto']['name']);
        $rutaFoto = 'uploads/' . $nombreArchivo;
        move_uploaded_file($_FILES['foto']['tmp_name'], $rutaFoto);
    } else {
        $rutaFoto = $datos['fotoPerfil'];
    }

    $usuarioViejo = $usuario;

    // --- Actualizar users ---
    $stmtUpdate = $conn->prepare("UPDATE users SET usuario=?, fotoPerfil=? WHERE usuario=?");
    $stmtUpdate->bind_param("sss", $nuevoUsuario, $rutaFoto, $usuarioViejo);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    // --- Actualizar recordatorios ---
    $stmtUpdate = $conn->prepare("UPDATE recordatorios SET usuario=? WHERE usuario=?");
    $stmtUpdate->bind_param("ss", $nuevoUsuario, $usuarioViejo);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    // --- Actualizar amigos ---
    $stmtUpdate = $conn->prepare("UPDATE amigos SET usuario=? WHERE usuario=?");
    $stmtUpdate->bind_param("ss", $nuevoUsuario, $usuarioViejo);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    $stmtUpdate = $conn->prepare("UPDATE amigos SET amigo=? WHERE amigo=?");
    $stmtUpdate->bind_param("ss", $nuevoUsuario, $usuarioViejo);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    // --- Actualizar comentarios ---
    $stmtUpdate = $conn->prepare("UPDATE comentarios SET usuario=? WHERE usuario=?");
    $stmtUpdate->bind_param("ss", $nuevoUsuario, $usuarioViejo);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    // Actualizar sesión y localStorage
    $_SESSION['usuario'] = $nuevoUsuario;
    echo "<script>
        localStorage.setItem('usuario', '" . addslashes($nuevoUsuario) . "');
        localStorage.setItem('fotoPerfil', '" . addslashes($rutaFoto) . "');
        alert('Perfil actualizado correctamente');
        window.location='perfil.php';
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil - ClassUp</title>
    <link rel="stylesheet" href="css/editar_perfil.css">
</head>
<body>
<div class="container">
    <div class="profile-section">
        <div class="profile-card recuadro-editar">
            <img src="<?= htmlspecialchars($datos['fotoPerfil'] ?: 'foto.jpg') ?>" alt="Foto actual" class="profile-pic">
            <div class="profile-info">
                <h2>@<?= htmlspecialchars($usuario) ?></h2>
                <p>Editar perfil</p>
            </div>

            <form method="post" enctype="multipart/form-data" class="form-editar">
                <label for="usuario">Nuevo nombre de usuario:</label>
                <input type="text" id="usuario" name="usuario" value="<?= htmlspecialchars($datos['usuario']) ?>" required>

                <label for="foto">Nueva foto de perfil:</label>
                <input type="file" name="foto" id="foto" accept="image/*">

                <button type="submit" class="boton-opcion">💾 Guardar cambios</button>
                <a href="perfil.php" class="boton-opcion boton-volver">↩ Volver</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
