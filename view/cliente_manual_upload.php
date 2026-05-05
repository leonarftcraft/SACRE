<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Subir Imagen Manual</title>
    <link href="view/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .mobile-card { width: 100%; max-width: 400px; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; }
        .hidden { display: none !important; }
        .btn-big { padding: 15px; font-size: 1.2rem; width: 100%; border-radius: 12px; margin-top: 15px; }
        .loader { border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <div class="mobile-card">
        <img src="view/images/logo.png" alt="Logo" style="height: 80px; margin-bottom: 20px;">
        <h4 class="mb-3">Cargar Imagen de Folio</h4>
        <p class="text-muted mb-4">Envía una foto del folio al formulario de registro manual.</p>
        
        <div class="mb-3">
            <label for="digitalizadorNombre" class="form-label">Tu Nombre:</label>
            <input type="text" id="digitalizadorNombre" class="form-control form-control-lg text-center" placeholder="Nombre del Digitalizador">
        </div>

        <script>
            // Restaurar nombre del digitalizador si existe en sessionStorage
            $(document).ready(function() {
                const savedName = sessionStorage.getItem('digitalizador_manual_name');
                if (savedName) $('#digitalizadorNombre').val(savedName);
            });
        </script>

        <input type="file" id="imageInput" accept="image/*" capture="environment" style="display: none;" onchange="handleImageSelection(this)">

        <button class="btn btn-primary btn-big" onclick="document.getElementById('imageInput').click()">
            📸 Seleccionar/Tomar Foto
        </button>

        <div id="loader" class="hidden">
            <div class="loader"></div>
            <p class="text-muted small">Subiendo imagen...</p>
        </div>

        <div id="statusMessage" class="mt-3 hidden"></div>
    </div>

    <script src="view/js/jquery-3.6.0.min.js"></script>
    <script src="view/js/sweetalert.js"></script>
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const sessionId = urlParams.get('session_id');

        if (!sessionId) {
            Swal.fire('Error', 'ID de sesión no encontrado. Por favor, acceda desde el QR o enlace del formulario principal.', 'error');
        }

        function handleImageSelection(input) {
            const file = input.files[0];
            if (!file) return;

            const digitalizadorNombre = $('#digitalizadorNombre').val().trim();
            if (!digitalizadorNombre) {
                Swal.fire('Error', 'Por favor, ingresa tu nombre.', 'warning');
                sessionStorage.removeItem('digitalizador_manual_name'); // Limpiar si no se ingresa
                input.value = ''; // Limpiar input para permitir re-selección
                return;
            }

            $('#loader').removeClass('hidden');
            $('#statusMessage').addClass('hidden').empty();

            sessionStorage.setItem('digitalizador_manual_name', digitalizadorNombre); // Guardar nombre
            const formData = new FormData();
            formData.append('imagen', file);
            formData.append('digitalizador_nombre', digitalizadorNombre);
            formData.append('session_id', sessionId);

            $.ajax({
                url: '?controller=sacrej&action=api_upload_manual_image',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(res) {
                    $('#loader').addClass('hidden');
                    Swal.fire(res.status === 'ok' ? 'Éxito' : 'Error', res.msg, res.status);
                    if (res.status === 'ok') {
                        $('#statusMessage').removeClass('hidden').html('<p class="text-success">Imagen enviada. Puede cerrar esta ventana.</p>');
                    }
                },
                error: function() {
                    $('#loader').addClass('hidden');
                    Swal.fire('Error', 'Error al subir la imagen.', 'error');
                }
            });
            input.value = ''; // Limpiar input para permitir tomar la misma foto de nuevo
        }
    </script>
</body>
</html>