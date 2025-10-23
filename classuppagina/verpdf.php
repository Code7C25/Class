<?php
$archivo = __DIR__ . '/Manual_de_Usuario_ClassUp.pdf'; 

if (file_exists($archivo)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($archivo) . '"');
    header('Content-Length: ' . filesize($archivo));
    readfile($archivo);
    exit;
} else {
    echo "⚠️ El archivo PDF no se encontró en: " . $archivo;
}
?>

