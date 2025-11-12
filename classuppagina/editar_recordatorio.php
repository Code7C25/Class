<?php
session_start();
$usuario = $_SESSION['usuario'] ?? $_COOKIE['usuario'] ?? null;
if (!$usuario) {
    die("Usuario no autenticado.");
}

$conexion = new mysqli("localhost", "root", "", "paginaclassup");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID de recordatorio no especificado.");
}

// Si se envía el formulario, actualiza los datos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $fecha = $_POST['fecha'];
    $hora_aviso = $_POST['hora_aviso'];
    $dias_antes = $_POST['dias_antes'];

    $sql = "UPDATE recordatorios 
            SET titulo=?, fecha=?, descripcion=?, dias_antes=?, hora_aviso=?, notificado=0 
            WHERE id=? AND usuario=?";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        die("Error al preparar la consulta: " . $conexion->error);
    }

    // 7 parámetros: sssisis (texto, texto, texto, int, texto, int, texto)
    $stmt->bind_param("sssisis", $titulo, $fecha, $descripcion, $dias_antes, $hora_aviso, $id, $usuario);

    if ($stmt->execute()) {
        header("Location: inicio.php");
        exit();
    } else {
        echo "Error al actualizar: " . $stmt->error;
    }

    $stmt->close();
}

// Cargar datos del recordatorio actual
$sql = "SELECT * FROM recordatorios WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$recordatorio = $resultado->fetch_assoc();

if (!$recordatorio) {
    die("Recordatorio no encontrado.");
}

// Cargar datos del recordatorio actual
$sql = "SELECT * FROM recordatorios WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$recordatorio = $resultado->fetch_assoc();

if (!$recordatorio) {
    die("Recordatorio no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Recordatorio</title>
<style>
body {
    background-color: #f9f4ef;
    font-family: 'Segoe UI', sans-serif;
    text-align: center;
    padding: 40px;
    color: #5c3a1d;
}
form {
    background-color: #fff;
    padding: 25px;
    border-radius: 15px;
    display: inline-block;
    box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
}
input, textarea {
    width: 90%;
    padding: 8px;
    margin: 10px 0;
    border-radius: 8px;
    border: 1px solid #d3b89f;
}
button {
    background-color: #c58f6c;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    cursor: pointer;
}
button:hover {
    background-color: #a97552;
}
</style>
</head>
<body>

<h2>✏️ Editar Recordatorio</h2>

<form method="POST">
    <label>Título:</label><br>
    <input type="text" name="titulo" value="<?php echo htmlspecialchars($recordatorio['titulo']); ?>" required><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" required><?php echo htmlspecialchars($recordatorio['descripcion']); ?></textarea><br>

    <label>Fecha:</label><br>
    <input type="date" name="fecha" value="<?php echo htmlspecialchars($recordatorio['fecha']); ?>" required><br>

    <label>Hora de aviso:</label><br>
    <input type="time" name="hora_aviso" value="<?php echo htmlspecialchars($recordatorio['hora_aviso']); ?>" required><br>

    <label>Días antes:</label><br>
    <input type="number" name="dias_antes" value="<?php echo htmlspecialchars($recordatorio['dias_antes']); ?>" min="0" required><br>

    <button type="submit">💾 Guardar cambios</button>
</form>

</body>
</html>
