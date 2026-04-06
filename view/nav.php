<?php
// ---------------------------------------------------------
// 🧩 NAVBAR PRINCIPAL - SACREJ
// ---------------------------------------------------------

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 🚫 Evitar que el nav se procese en llamadas AJAX
// Esto previene errores o redirecciones no deseadas
$isAjax = (
    isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
);

// Si es una llamada AJAX, salimos antes de generar HTML
if ($isAjax) return;

// ---------------------------------------------------------
// 🔹 Datos de sesión y roles
// ---------------------------------------------------------
$rolId  = $_SESSION['RolUsu'] ?? 0;
$nombre = $_SESSION['NomUsu'] ?? 'Usuario';

$roles = [
    10 => 'Administrador',
    20 => 'Ministro',
    30 => 'Secretaria',
    40 => 'Coordinadora de Catequesis',
    50 => 'Catequista'
];
$rolNombre = $roles[$rolId] ?? 'Invitado';
?>
<nav class="navbar navbar-expand-lg navbar-custom navbar-dark fixed-top shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="?controller=sacrej&action=index">
      <img src="view/images/logo.png" alt="Logo sacrej" style="height: 70px; width: auto; margin-right: 20px;">
      SACRA</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">

        <!-- 🔹 Inicio -->
        <li class="nav-item">
          <a class="nav-link" href="?controller=sacrej&action=index">Inicio</a>
        </li>

        <!-- 🔹 Dropdown Gestión -->
        <?php if (in_array($rolId, [10,20,30,40,50])): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="gestionDropdown" data-bs-toggle="dropdown">
            Gestión
          </a>
          <ul class="dropdown-menu" aria-labelledby="gestionDropdown">
            <?php if (in_array($rolId, [10,20,30])): ?>
              <li><a class="dropdown-item" href="?controller=sacrej&action=vista_bautizos">Registrar Bautizo</a></li>
              <li><a class="dropdown-item" href="?controller=sacrej&action=vista_celebraciones_registradas">Celebraciones Registradas</a></li>
              <li><a class="dropdown-item" href="?controller=sacrej&action=vista_desplegar_server">Desplegar Server</a></li>
              <li><a class="dropdown-item" href="#" onclick="abrirConstancia()">Constancia de no asentamiento</a></li>
            <?php elseif ($rolId == 40): ?>
              <li><a class="dropdown-item" href="#">Gestión de Clases</a></li>
            <?php elseif ($rolId == 50): ?>
              <li><a class="dropdown-item" href="#">Material de Catequesis</a></li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- 🔹 Dropdown Vistas (solo admin) -->
        <?php if ($rolId == 10): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="vistasDropdown" data-bs-toggle="dropdown">
            Vistas
          </a>
          <ul class="dropdown-menu" aria-labelledby="vistasDropdown">
            <li><a class="dropdown-item" href="?controller=sacrej&action=vista_ministros">Ministros</a></li>
            <li><a class="dropdown-item" href="?controller=sacrej&action=vista_jerarquias">Jerarquías</a></li>
            <li><a class="dropdown-item" href="index.php?controller=sacrej&action=vista_celebraciones">Celebraciones</a></li>
          </ul>
        </li>

        <!-- 🔹 Dropdown Configuraciones (solo admin) -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="configDropdown" data-bs-toggle="dropdown">
            Configuraciones
          </a>
          <ul class="dropdown-menu" aria-labelledby="configDropdown">
            <li><a class="dropdown-item" href="?controller=sacrej&action=vista_usuarios">Usuarios</a></li>
            <li><a class="dropdown-item" href="?controller=sacrej&action=vista_administrar_api">Administrar API Key Gemini</a></li>
            <li><a class="dropdown-item" href="?controller=sacrej&action=vista_respaldo">Respaldo</a></li>
          </ul>
        </li>
        <?php endif; ?>

        <!-- 🔹 Perfil -->
        <li class="nav-item">
          <a class="nav-link" href="#">Perfil</a>
        </li>

        <!-- 🔹 Cerrar o iniciar sesión -->
        <?php if (!empty($_SESSION['IdUsu'])): ?>
          <li class="nav-item">
            <a class="nav-link text-danger" href="?controller=sacrej&action=cerrarSesion">Cerrar Sesión</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="?controller=sacrej&action=iniciar">Iniciar Sesión</a>
          </li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>
