<?php
// Conexión con la base de datos
$conexion = new mysqli("localhost", "root", "", "paginaclassup");
if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
}

session_start();
if (!isset($_SESSION['usuario_id'])) {
    exit(); // Solo mostrar notificaciones si el usuario está logueado
}

$usuario_id = $_SESSION['usuario_id'];
$hoy = date('Y-m-d');
$hora_actual = date('H:i');

// Buscar recordatorios activos y no notificados del usuario
$sql = "SELECT id, titulo, descripcion, fecha, hora, dias_antes, hora_aviso 
        FROM recordatorios 
        WHERE usuario_id = ? 
          AND activado = 1 
          AND notificado = 0";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

while ($row = $resultado->fetch_assoc()) {
    $fecha_evento = $row['fecha'];
    $hora_evento = $row['hora'];
    $dias_antes = $row['dias_antes'];
    $hora_aviso = $row['hora_aviso'];

    // Calcular fecha del aviso
    $fecha_aviso = date('Y-m-d', strtotime("-$dias_antes days", strtotime($fecha_evento)));

    // Si hoy es la fecha del aviso y la hora actual >= hora_aviso → mostrar notificación
    if ($hoy == $fecha_aviso && $hora_actual >= $hora_aviso) {
        echo "
        <div style='
            background-color:#fff6e5;
            border:1px solid #e6c87b;
            border-radius:12px;
            padding:15px;
            margin:15px auto;
            width:70%;
            font-family:sans-serif;
            box-shadow:0 2px 5px rgba(0,0,0,0.1);
        '>
            <h3 style='color:#8b5e34;'>⏰ Recordatorio Próximo: {$row['titulo']}</h3>
            <p>{$row['descripcion']}</p>
            <small>📅 Fecha del evento: {$row['fecha']} — ⌚ {$row['hora']}</small>
        </div>
        ";

        // Marcar como notificado
        $update = $conexion->prepare("UPDATE recordatorios SET notificado = 1 WHERE id = ?");
        $update->bind_param("i", $row['id']);
        $update->execute();
    }
}

$stmt->close();
$conexion->close();
?>
