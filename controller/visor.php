<?php
// 🛡️ Evitar que cualquier error de PHP se muestre y corrompa la imagen
error_reporting(0);
ob_start();

/**
 * Visor de Imágenes SACRE
 * Permite leer archivos .dat y servirlos como imágenes JPEG
 * Uso: visor.php?img=view/images/actas/Libro_1/Folios_1-30/archivo.dat
 */

// Cargar la configuración para obtener la llave XOR
require_once "../config.php";

$archivo = $_GET['img'] ?? '';
// 📂 Ajustar ruta: El archivo se solicita relativo a la raíz, pero el visor está en /controller
$rutaReal = "../" . $archivo;

// 🛡️ Seguridad: Solo permitir acceso a la carpeta de actas y validar que el archivo exista
if (!empty($archivo) && strpos($archivo, 'view/images/actas/') === 0 && file_exists($rutaReal)) {
    
    // Limpiar el búfer por si config.php tiene espacios accidentales
    ob_clean();

    $data = file_get_contents($rutaReal);
    if ($data === false) {
        header("HTTP/1.0 404 Not Found");
        exit;
    }
    
    // 🛡️ RECUPERACIÓN: Aplicamos XOR con la llave centralizada en config.php
    $xorKey = defined('SACRE_XOR_KEY') ? SACRE_XOR_KEY : 0x00;
    $len = strlen($data);
    
    // Solo transformamos los primeros 10 bytes (el "disfraz")
    if ($len > 0) {
        for ($i = 0; $i < 10 && $i < $len; $i++) {
            $data[$i] = chr(ord($data[$i]) ^ $xorKey);
        }
    }

    // 🖼️ Cabeceras para forzar la visualización en el navegador
    header_remove(); 
    header("Content-Type: image/jpeg");
    header("Content-Disposition: inline; filename=imagen.jpg");
    header("Content-Length: " . $len);
    header("X-Content-Type-Options: nosniff"); // Evita que el navegador "adivine" que es un .dat
    header("Cache-Control: public, max-age=86400");
    header("Pragma: public");

    echo $data;
    ob_end_flush();
    exit;
} else {
    ob_end_clean();
    header("HTTP/1.0 404 Not Found");
    echo "Imagen no encontrada o acceso denegado.";
    exit;
}
?>