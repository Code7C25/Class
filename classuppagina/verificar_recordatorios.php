<?php
include 'conexion.php';
session_start();

$usuario = $_SESSION['usuario'] ?? $_COOKIE['usuario'] ?? null;
if (!$usuario) { echo json_encode([]); exit(); }

$sql = "SELECT id, titulo, fecha, hora, descripcion, dias_antes, hora_aviso 
        FROM recordatorios 
        WHERE usuario = ? 
          AND activado = 1 
          AND (notificado IS NULL OR notificado = 0)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$res = $stmt->get_result();

$notificaciones = [];
$ahora = time();
$ventana = 15 * 60; // 15 minutos de tolerancia

while ($r = $res->fetch_assoc()) {
    $fechaEvento = strtotime($r['fecha'] . ' ' . ($r['hora'] ?: '00:00'));
    $diasAntes = intval($r['dias_antes']);
    $horaAviso = $r['hora_aviso'] ?: '08:00';
    [$hAviso, $mAviso] = explode(':', $horaAviso);

    // Calcular fecha y hora exacta de aviso
    $fechaAviso = strtotime("-$diasAntes days", $fechaEvento);
    $fechaAviso = strtotime(date("Y-m-d", $fechaAviso) . " $hAviso:$mAviso");

    // Notificar si estamos dentro de la ventana de aviso
    if ($ahora >= $fechaAviso && $ahora < $fechaEvento + $ventana) {
        $notificaciones[] = $r;

        // Marcar como notificado para no repetir
        $upd = $conn->prepare("UPDATE recordatorios SET notificado = 1 WHERE id = ?");
        $upd->bind_param("i", $r['id']);
        $upd->execute();
        $upd->close();
    }
}

echo json_encode($notificaciones);
?>
