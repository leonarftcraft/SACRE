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
<body class="p-3">
    <div class="mobile-card">
        <img src="view/images/logo.png" alt="Logo" style="height: 80px; margin-bottom: 20px;">
        <h4 class="mb-3">Cargar Imagen de Folio</h4>
        <p class="text-muted mb-4">Envía una foto del folio al formulario de registro manual.</p>
        
        <div class="mb-3" id="digitalizadorField">
            <label for="digitalizadorNombre" class="form-label">Tu Nombre:</label>
            <input type="text" id="digitalizadorNombre" class="form-control form-control-lg text-center" placeholder="Nombre del Digitalizador">
        </div>

        <input type="file" id="imageInput" accept="image/*" capture="environment" style="display: none;" onchange="handleImageSelection(this)">

        <button id="btnUpload" class="btn btn-primary btn-big" onclick="document.getElementById('imageInput').click()">
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
        const digitalizadorFromUrl = urlParams.get('digitalizador');

        if (digitalizadorFromUrl) {
            $('#digitalizadorField').hide();
        }

        // 🛡️ Verificar periódicamente si la sesión sigue permitida por el servidor
        function verificarEstadoSesion() {
            if (!sessionId) return;
            $.getJSON('?controller=sacrej&action=api_verificar_sesion_manual', { session_id: sessionId }, function(res) {
                if (!res.active) {
                    $('body').html('<div class="d-flex align-items-center justify-content-center" style="height:100vh; background:#f0f2f5;"><div class="mobile-card"><h3>Sesión Finalizada</h3><p class="text-muted">La ventana de recepción se ha cerrado o ha expirado. Por favor, solicite un nuevo código QR.</p></div></div>');
                    return;
                }

                // 🆕 Sincronizar estado del botón: si ya hay una imagen en el servidor, deshabilitar
                $.post('?controller=sacrej&action=api_check_manual_upload_status', { session_id: sessionId }, function(statusRes) {
                    if (statusRes.status === 'ready') {
                        $('#btnUpload').prop('disabled', true).html('✅ Imagen Enviada').removeClass('btn-primary').addClass('btn-success');
                        $('#statusMessage').removeClass('hidden').html('<p class="text-success">Imagen enviada correctamente. El administrador la está revisando.</p>');
                    } else {
                        $('#btnUpload').prop('disabled', false).html('📸 Seleccionar/Tomar Foto').removeClass('btn-success').addClass('btn-primary');
                        if ($('#statusMessage .text-success').length) $('#statusMessage').addClass('hidden').empty();
                    }
                }, 'json');
            });
        }
        setInterval(verificarEstadoSesion, 3000);

        function handleImageSelection(input) {
            const file = input.files[0];
            if (!file) return;

            let digitalizadorNombre = digitalizadorFromUrl || $('#digitalizadorNombre').val().trim();
            if (!digitalizadorNombre) {
                Swal.fire('Atención', 'Por favor, ingresa tu nombre.', 'warning');
                input.value = '';
                return;
            }

            $('#loader').removeClass('hidden');
            $('#statusMessage').addClass('hidden').empty();

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
                    if (res.status === 'ok') {
                        Swal.fire('Éxito', 'Imagen enviada correctamente.', 'success');
                        $('#statusMessage').removeClass('hidden').html('<p class="text-success">Imagen enviada. Puede cerrar esta ventana o esperar si necesita subir otra.</p>');
                        $('#btnUpload').prop('disabled', true).html('✅ Imagen Enviada').removeClass('btn-primary').addClass('btn-success');
                        input.value = '';
                    } else {
                        Swal.fire('Error', res.msg, 'error');
                    }
                },
                error: function() {
                    $('#loader').addClass('hidden');
                    Swal.fire('Error', 'Error al conectar con el servidor.', 'error');
                }
            });
        }
    </script>
</body>
</html>