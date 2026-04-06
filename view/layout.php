<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SACRA - Sistema Parroquial</title>

    <!-- 🔹 CSS -->
    <link href="view/css/bootstrap.min.css" rel="stylesheet">
    <link href="view/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="view/css/bootstrap-icons.min.css">


    <!-- 🔹 JS global para vistas internas (ANTES de las vistas) -->
    <script src="view/js/jquery-3.6.0.min.js"></script>
    <script src="view/js/bootstrap.bundle.min.js"></script>
    <script src="view/js/sweetalert.js"></script>
    <script src="view/js/sweetalert2.all.min.js"></script>
</head>

<body class="layout-interno d-flex flex-column min-vh-100" style="background-color:#f8f7f7;">

    <!-- 🔸 Barra de navegación -->
    <?php require_once "view/nav.php"; ?>

    <!-- 🔸 Contenido dinámico -->
    <main class="container my-4">
        <?php
        if (isset($contenido)) {
            require_once $contenido;
        } else {
            echo "<p class='text-center text-muted'>No se ha definido contenido para esta vista.</p>";
        }
        ?>
    </main>

    <div class="modal fade" id="modalConstancia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

        <form method="POST" action="index.php?controller=sacrej&action=generar_constancia_no_asentamiento">

            <div class="modal-header">
            <h5 class="modal-title">Constancia de No Asentamiento</h5>
            </div>

            <div class="modal-body">

            <!-- 👤 Ministro -->
            <label>Ministro firmante</label>
            <select name="idUsu" class="form-control" required>
                <?php
                $ministros = $this->model->obtener_ministros_firmantes();
                while($m = $ministros->fetch_assoc()){
                    echo "<option value='{$m['IdUsu']}'>
                            {$m['NomUsu']} {$m['ApeUsu']}
                            </option>";
                }
                ?>
            </select>

            <!-- 👶 Nombre -->
            <label>Nombre completo</label>
            <input type="text" name="nombre" class="form-control" required>


            </div>
                
            <div class="modal-footer">
            <button type="submit" class="btn btn-primary">
                Generar PDF
            </button>
            </div>

        </form>

        </div>
    </div>
    </div>

    <!-- 🔸 Pie de página -->
    <?php require_once "view/footer.php"; ?>

    <script>
        function abrirConstancia() {
            const modal = new bootstrap.Modal(document.getElementById('modalConstancia'));
            modal.show();
        }
    </script>

</body>
</html>
