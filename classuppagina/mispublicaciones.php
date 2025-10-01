<?php
session_start();

if (!isset($_SESSION['nick'])) {
  echo "<p style='text-align:center;'>Debes iniciar sesión para ver tus publicaciones.</p>";
  exit();
}

$conn = mysqli_connect("localhost", "root", "", "bdblog");

if (!$conn) {
  die("Error de conexión: " . mysqli_connect_error());
}

$nick = $_SESSION['nick'];
$query = "SELECT * FROM mispost WHERE nick = '$nick' ORDER BY fecha DESC";
$resultado = mysqli_query($conn, $query);

if (!$resultado) {
  die("Error en la consulta: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Posts</title>
  <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="css/mispublicaciones.css">
</head>
<body>
  <header>
    <div class="titulo-pagina">📜 Mis Publicaciones</div>
  </header>

  <div class="posts">
    <?php
    if (mysqli_num_rows($resultado) > 0) {
      while ($fila = mysqli_fetch_assoc($resultado)) {
        echo "<div class='post'>";
        echo "<div class='titulo'>" . htmlspecialchars($fila['titulo']) . "</div>";
        echo "<div class='texto'>" . nl2br(htmlspecialchars($fila['cuerpo'])) . "</div>";
        if (!empty($fila['imagen'])) {
          echo "<img class='imagen-post' src='imagenes/" . htmlspecialchars($fila['imagen']) . "' alt='Imagen del post'>";
        }
        echo "<div class='fecha'>Publicado el: " . htmlspecialchars($fila['fecha']) . "</div>";
        echo "</div>";
      }
    } else {
      echo "<p class='mensaje-vacio'>Todavía no publicaste ningún post.</p>";
    }
    ?>
  </div>

  <div class="volver">
    <a href="inicio.html">← Volver al inicio</a>
  </div>
</body>
</html>
