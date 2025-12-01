<?php
/**
 * Generador de Hash de Contraseña Seguro
 *
 * Ejecute este script desde su servidor PHP para generar un hash seguro
 * para la contraseña del administrador.
 *
 * Uso:
 * 1. Coloque este archivo en la raíz de su proyecto.
 * 2. Acceda a él desde su navegador (ej. http://localhost/tools/generar_hash.php).
 * 3. Copie el hash generado.
 * 4. Péguelo en el archivo `sacrej.sql`, reemplazando el hash existente para el usuario 'Admin01'.
 * 5. Una vez obtenido el hash, elimine este archivo de su servidor por seguridad.
 */

// La contraseña para la que se generará el hash
$contrasena = 'Admin1234.';

// Generar el hash usando el algoritmo BCRYPT (el predeterminado y recomendado)
$hash = password_hash($contrasena, PASSWORD_DEFAULT);

// Mostrar el resultado en un formato claro y fácil de copiar
header('Content-Type: text/plain; charset=utf-8');
echo "=========================================================\n";
echo "           GENERADOR DE HASH DE CONTRASEÑA\n";
echo "=========================================================\n\n";
echo "Contraseña a hashear: ". $contrasena. "\n\n";
echo "Hash generado (copie esta línea completa):\n";
echo $hash. "\n\n";
echo "=========================================================\n";
echo "INSTRUCCIONES:\n";
echo "1. Copie el hash de arriba.\n";
echo "2. Abra el archivo `sacrej.sql`.\n";
echo "3. Reemplace el valor de `ClaUsu` para el usuario 'Admin01' con este hash.\n";
echo "4. IMPORTANTE: Elimine este archivo (`tools/generar_hash.php`) de su servidor.\n";
echo "=========================================================\n";

?>
