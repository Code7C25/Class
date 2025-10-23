<?php
// Conexión a la base de datos
include 'conexion.php'; // ajusta el nombre si tu archivo es distinto

session_start();
$usuario = $_SESSION['usuario'] ?? '';

if (!$usuario) {
    header("Location: inicio.html");
    exit;
}

// Obtener datos actuales del usuario
$sql = "SELECT NOMBRE, APELLIDO, MAIL, FOTO FROM usuarios WHERE NICKNAME=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$datos = $result->fetch_assoc();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $mail = $_POST['mail'];

    // Subida de foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $rutaFoto = 'uploads/' . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], $rutaFoto);
    } else {
        $rutaFoto = $datos['FOTO'];
    }

    $sql = "UPDATE usuarios SET NOMBRE=?, APELLIDO=?, MAIL=?, FOTO=? WHERE NICKNAME=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nombre, $apellido, $mail, $rutaFoto, $usuario);
    $stmt->execute();

    header("Location: perfil.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Perfil - ClassUp</title>
<link rel="stylesheet" href="css/perfil.css">
</head>
<body>
  <div class="container">
    <h2>Editar Perfil</h2>
    <form method="post" enctype="multipart/form-data">
      <label>Nombre:</label>
      <input type="text" name="nombre" value="<?= htmlspecialchars($datos['NOMBRE']) ?>" required>
      <label>Apellido:</label>
      <input type="text" name="apellido" value="<?= htmlspecialchars($datos['APELLIDO']) ?>" required>
      <label>Email:</label>
      <input type="email" name="mail" value="<?= htmlspecialchars($datos['MAIL']) ?>" required>
      <label>Foto de perfil:</label>
      <input type="file" name="foto">
      <button type="submit" class="boton-opcion">Guardar cambios</button>
    </form>
  </div>
</body>
</html>
