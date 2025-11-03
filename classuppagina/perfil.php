<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION['usuario'];
$fotoPerfil = $_SESSION['fotoPerfil'] ?? 'default.png';

// Obtener recordatorios del usuario
$sql = "SELECT titulo, descripcion, fecha, hora FROM recordatorios WHERE usuario = ? ORDER BY fecha, hora";
$stmt = $conn->prepare($sql);
if(!$stmt){
    die("Error en la consulta: " . $conn->error);
}
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$recordatorios = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil - ClassUp</title>
    <link rel="stylesheet" href="css/perfil.css">
</head>
<body>
<div class="container">
    <div class="profile-section">
        <div class="profile-card">
            <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto de perfil" class="profile-pic">
            <div class="profile-info">
                <h2>@<?= htmlspecialchars($usuario) ?></h2>
                <a href="editar_perfil.php" class="boton-opcion">✏️ Editar Perfil</a>
            </div>
        </div>
    </div>

    <hr class="divider">

    <div class="calendar-section">
        <div id="calendar-header">
            <button id="prev-month" onclick="cambiarMes(-1)">◀</button>
            <span id="month-year"></span>
            <button id="next-month" onclick="cambiarMes(1)">▶</button>
        </div>

        <div style="display:flex; justify-content:center; gap:10px; margin-bottom:10px;">
            <select id="select-mes" onchange="irAMes()">
                <option value="0">Enero</option>
                <option value="1">Febrero</option>
                <option value="2">Marzo</option>
                <option value="3">Abril</option>
                <option value="4">Mayo</option>
                <option value="5">Junio</option>
                <option value="6">Julio</option>
                <option value="7">Agosto</option>
                <option value="8">Septiembre</option>
                <option value="9">Octubre</option>
                <option value="10">Noviembre</option>
                <option value="11">Diciembre</option>
            </select>
            <select id="select-anio" onchange="irAMes()"></select>
        </div>

        <table id="tabla-calendario">
            <thead>
                <tr>
                    <th>Lun</th>
                    <th>Mar</th>
                    <th>Mié</th>
                    <th>Jue</th>
                    <th>Vie</th>
                    <th>Sáb</th>
                    <th>Dom</th>
                </tr>
            </thead>
            <tbody id="cuerpo-calendario"></tbody>
        </table>
    </div>
</div>

<!-- Menú inferior -->
<div class="cuadro-opciones">
    <a href="inicio.php" class="boton-opcion">🏠 Inicio</a>
    <a href="cerrarsesion.html" class="boton-opcion">🚪 Cerrar sesión</a>
    <a href="configuracion.html" class="boton-opcion">⚙️ Ajustes</a>
</div>

<script>
const cuerpoCalendario = document.getElementById("cuerpo-calendario");
const mesAnio = document.getElementById("month-year");
const selectMes = document.getElementById("select-mes");
const selectAnio = document.getElementById("select-anio");

let fecha = new Date();
let mesActual = fecha.getMonth();
let anioActual = fecha.getFullYear();

// Recordatorios pasados desde PHP a JS
const recordatorios = <?= json_encode($recordatorios) ?>;

for (let y = anioActual - 10; y <= anioActual + 10; y++) {
    let option = document.createElement("option");
    option.value = y;
    option.textContent = y;
    if (y === anioActual) option.selected = true;
    selectAnio.appendChild(option);
}

function generarCalendario(mes, anio) {
    cuerpoCalendario.innerHTML = "";
    let primerDia = new Date(anio, mes, 1).getDay();
    let diasMes = new Date(anio, mes + 1, 0).getDate();
    let offset = primerDia === 0 ? 6 : primerDia - 1;

    mesAnio.textContent = `${selectMes.options[mes].text} ${anio}`;
    selectMes.value = mes;
    selectAnio.value = anio;

    let fila = document.createElement("tr");
    for (let i = 0; i < offset; i++) fila.appendChild(document.createElement("td"));

    for (let dia = 1; dia <= diasMes; dia++) {
        if (fila.children.length === 7) {
            cuerpoCalendario.appendChild(fila);
            fila = document.createElement("tr");
        }
        let celda = document.createElement("td");
        celda.textContent = dia;

        // Resaltar hoy
        let hoy = new Date();
        if (dia === hoy.getDate() && mes === hoy.getMonth() && anio === hoy.getFullYear())
            celda.classList.add("hoy");

        // Agregar recordatorios del día
        recordatorios.forEach(r => {
            let rFecha = new Date(r.fecha);
            if(rFecha.getFullYear() === anio && rFecha.getMonth() === mes && rFecha.getDate() === dia){
                let div = document.createElement("div");
                div.classList.add("recordatorio-dia");
                div.textContent = r.titulo;
                celda.appendChild(div);
            }
        });

        fila.appendChild(celda);
    }
    while (fila.children.length < 7) fila.appendChild(document.createElement("td"));
    cuerpoCalendario.appendChild(fila);
}

function cambiarMes(delta) {
    mesActual += delta;
    if (mesActual < 0) { mesActual = 11; anioActual--; }
    else if (mesActual > 11) { mesActual = 0; anioActual++; }
    generarCalendario(mesActual, anioActual);
}

function irAMes() {
    mesActual = parseInt(selectMes.value);
    anioActual = parseInt(selectAnio.value);
    generarCalendario(mesActual, anioActual);
}

generarCalendario(mesActual, anioActual);
</script>
</body>
</html>
