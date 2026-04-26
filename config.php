<?php
// Archivo de configuración local - NO SUBIR A GIT
// Define la llave maestra de encriptación para el sistema
define('SACRE_ENC_KEY', 'CLAVE_SECRETA_SACREJ_2025_SEGURA');
// Llave para el disfraz XOR de archivos .dat
define('SACRE_XOR_KEY', 0x3F);
// No cerrar la etiqueta PHP para evitar espacios/saltos de línea accidentales