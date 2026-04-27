<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<div class="container mt-4">
    <h2 class="text-center mb-4 text-primary">Copias de Seguridad y Respaldo</h2>
    <p class="text-center text-muted mb-5">Gestione las copias de seguridad de los archivos digitales y la base de datos del sistema.</p>

    <div class="row g-4">
        <!-- 📦 Respaldo Completo (Local) -->
        <div class="col-md-12">
            <div class="card shadow h-100 border-0">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h5 class="mb-0"><i class="bi bi-archive-fill"></i> Respaldo Completo (Local)</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="card-text text-center mt-3">
                        Esta opción generará y descargará un archivo comprimido (<strong>.zip</strong>) que contiene:
                        <ul class="text-start small text-muted mx-auto" style="max-width: 80%;">
                            <li>📂 Todas las imágenes de actas digitalizadas.</li>
                            <li>🗄️ Archivo SQL con la base de datos completa.</li>
                        </ul>
                    </p>
                    <div class="alert alert-info small mt-auto">
                        <i class="bi bi-info-circle-fill"></i> Guarde este archivo en una ubicación segura designada por la administración.
                    </div>
                    <button onclick="solicitarRespaldo()" class="btn btn-primary w-100 py-2 fw-bold">
                         Generar y Descargar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
<script>
/**
 * Ejecuta la llamada al controlador para el respaldo fragmentado en la nube
 */
function ejecutarRespaldoNube() {
    Swal.fire({
        title: '¿Iniciar respaldo en la nube?',
        text: "Se generará un paquete comprimido y se subirá fragmentado a las cuentas de Google Drive configuradas. Esto puede tardar unos minutos dependiendo del tamaño de las actas.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, iniciar sincronización',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sincronizando...',
                html: 'Comprimiendo, fragmentando y subiendo partes a la nube.<br><b>Por favor, no cierre esta ventana.</b>',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.post('?controller=sacrej&action=api_respaldo_nube', function(res) {
                Swal.close();
                if (res.status === 'ok') {
                    Swal.fire('Éxito', res.msg, 'success');
                } else if (res.status === 'partial') {
                    // Generar lista de errores específicos devueltos por el controlador
                    let detalleHtml = '<div class="text-start mt-2 small text-danger"><b>Detalles del error:</b><ul class="mb-0">';
                    if (res.detalles && res.detalles.length > 0) {
                        res.detalles.forEach(err => detalleHtml += `<li>${err}</li>`);
                    } else {
                        detalleHtml += '<li>Error de autenticación general con Google Drive.</li>';
                    }
                    detalleHtml += '</ul></div>';

                    Swal.fire({
                        title: 'Respaldo Fallido/Parcial',
                        html: res.msg + detalleHtml + '<br><small class="text-muted">Verifique que las Service Accounts tengan habilitado el acceso a Google Drive API.</small>',
                        icon: 'warning'
                    });
                } else {
                    Swal.fire('Error', res.msg || 'No se pudo completar el respaldo.', 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error Crítico', 'El tiempo de espera se agotó o el servidor no respondió. El archivo podría ser demasiado grande para el tiempo de ejecución de PHP.', 'error');
            });
        }
    });
}
</script>