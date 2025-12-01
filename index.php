<?php

/**
 * ---------------------------------------------------------
 * 🚀 INDEX PRINCIPAL MVC - SACREJ (Versión Final Corregida)
 * ---------------------------------------------------------
 * - Enrutador principal del sistema
 * - Distingue entre peticiones normales y AJAX
 * - Fuerza manejo correcto de solicitudes POST (para AJAX)
 * - Evita contaminación de JSON y mantiene vistas limpias
 * ---------------------------------------------------------
 */

ob_start(); // 🔹 Control del buffer de salida

$controller = "sacrej";

/* ==========================================================
   🚩 Controlador por defecto
   ========================================================== */
if (!isset($_REQUEST["controller"])) {

    require_once "controller/$controller.controller.php";
    $controllerClass = ucwords($controller) . "Controller";
    $controllerObj = new $controllerClass();
    $controllerObj->index();

} else {

    /* ==========================================================
       🧩 Cargar controlador y acción solicitada
       ========================================================== */
    $controller = strtolower($_REQUEST["controller"]);
    $accion = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "index";
    $controllerPath = "controller/$controller.controller.php";

    if (!file_exists($controllerPath)) {
        die("❌ Error: El controlador <b>$controllerPath</b> no existe.");
    }

    require_once $controllerPath;
    $controllerClass = ucwords($controller) . "Controller";

    if (!class_exists($controllerClass)) {
        die("❌ Error: La clase <b>$controllerClass</b> no está definida.");
    }

    $controllerObj = new $controllerClass();

    if (!method_exists($controllerObj, $accion)) {
        die("❌ Error: La acción <b>$accion</b> no existe en <b>$controllerClass</b>.");
    }

    /* ==========================================================
       ⚙️ Detección de peticiones AJAX o POST
       ========================================================== */
    $isAjax = false;

    // 🔹 Detectar cabecera XMLHttpRequest
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        $isAjax = true;
    }

    // 🔹 Detectar acciones POST (como agregar, guardar, etc.)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $isAjax = true; // 🔥 Fuerza modo AJAX para peticiones POST
    }

    /* ==========================================================
       🚀 Ejecutar acción correspondiente
       ========================================================== */
    if ($isAjax) {
        // 🔸 Limpieza de salida previa (evita que HTML contamine JSON)
        if (ob_get_length()) ob_clean();

        header('Content-Type: application/json; charset=utf-8');

        try {
            call_user_func([$controllerObj, $accion]);
        } catch (Throwable $e) {
            echo json_encode([
                'status' => 'error',
                'msg' => 'Error interno en la ejecución: ' . $e->getMessage()
            ]);
        }

        exit; // ⚠️ Corta flujo para no incluir layout

    } else {
        // 🔸 Acción normal (renderiza vistas con layout)
        call_user_func([$controllerObj, $accion]);
    }
}

ob_end_flush();
?>
