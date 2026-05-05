<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<div class="container mt-4">
    <h4 class="text-center mb-4">Configuración de Google Drive API</h4>

    <div class="row">
        <!-- 📝 Formulario -->
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 fs-6">Vincular Proyecto de Drive</h5>
                </div>
                <div class="card-body">
                    <form id="formDriveCred" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Correo Electrónico</label>
                            <div class="input-group input-group-sm">
                                <select id="driveEmailSelect" class="form-select" onchange="toggleManualEmail(this.value)">
                                    <option value="">Seleccione o ingrese manual...</option>
                                    <?php foreach ($apiKeysGemini as $k): ?>
                                        <option value="<?= htmlspecialchars($k['email']) ?>"><?= htmlspecialchars($k['email']) ?> (Gemini)</option>
                                    <?php endforeach; ?>
                                    <option value="manual">-- Ingresar otro correo --</option>
                                </select>
                            </div>
                            <input type="email" id="driveEmailManual" class="form-control form-control-sm mt-2 d-none" placeholder="ejemplo@correo.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Archivo JSON de Credenciales (Opcional)</label>
                            <input type="file" id="jsonFile" class="form-control form-control-sm" accept=".json">
                            <div class="form-text" style="font-size: 0.7rem;">Si sube el archivo, el sistema intentará extraer el ID y el Secreto automáticamente.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Dirección de Carpeta (Drive ID)</label>
                            <input type="text" id="folderAddress" class="form-control form-control-sm" placeholder="Pegue aquí el ID de la carpeta de Drive">
                            <div class="form-text" style="font-size: 0.7rem;">Para Shared Drives, use el ID del Shared Drive. Asegúrese de agregar el email de la Service Account como miembro del Shared Drive con permisos de Editor.</div>
                        </div>

                        <div class="d-grid">
                            <button type="button" id="btnGuardarDrive" class="btn btn-success">Guardar Credenciales</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 🛠️ Utilidad Rclone -->
            <div class="card shadow-sm mt-3 border-info">
                <div class="card-header bg-info text-white py-1">
                    <h5 class="mb-0 fs-6">Utilidad Rclone</h5>
                </div>
                <div class="card-body py-2 text-center">
                    <p id="rclone-status-text" class="small mb-2">Verificando Rclone...</p>
                    <button type="button" id="btnToggleRcloneService" class="btn btn-sm mb-2" onclick="toggleRcloneService()">Verificando Servicio...</button>
                    <button type="button" id="btnInstalarRclone" class="btn btn-sm btn-primary" style="display:none;">Instalar Rclone en Servidor</button>
                </div>
            </div>
        </div>

        <!-- 📋 Tabla -->
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 fs-6">Proyectos Registrados</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Correo</th>
                                    <th>Nombre Remoto</th>
                                    <th>Dirección Carpeta</th>
                                    <th>JSON?</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($driveCreds)): ?>
                                    <?php foreach ($driveCreds as $c): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($c['email']) ?></td>
                                            <td class="fw-bold text-primary"><?= htmlspecialchars($c['remote_name'] ?? 'Pendiente') ?></td>
                                            <td class="text-truncate" style="max-width: 150px;"><?= htmlspecialchars($c['folder_address']) ?></td>
                                            <td class="text-center"><?= !empty($c['json_file']) ? '✅' : '❌' ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary" onclick="crearRemote('<?= $c['email'] ?>')" title="Crear Remote Rclone">🛠️</button>
                                                <button class="btn btn-sm btn-outline-success" onclick="probarSubida('<?= $c['email'] ?>')" title="Subir Archivo de Prueba">📤</button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="eliminarDrive('<?= $c['email'] ?>')">🗑️</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted">No hay credenciales registradas.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let rcloneServiceActivo = false;

// Lógica para Rclone
function verificarRclone() {
    // 1. Primero verificamos si el microservicio (Python) está corriendo en el puerto 5001
    $.getJSON('?controller=sacrej&action=api_verificar_estado_server_rclone', function(res) {
        rcloneServiceActivo = res.activo;
        const btn = $('#btnToggleRcloneService');
        
        if (rcloneServiceActivo) {
            btn.text('Desactivar Servicio Rcloner').removeClass('btn-success btn-secondary').addClass('btn-danger');
            
            // 2. Si el servicio está activo, verificamos si rclone.exe está instalado físicamente
            $.getJSON('?controller=sacrej&action=api_verificar_rclone', function(rRes) {
                if (rRes.installed) {
                    $('#rclone-status-text').html('<span class="text-success"><i class="fas fa-check"></i> Rclone instalado en ' + rRes.path + '</span>');
                    $('#btnInstalarRclone').hide();
                } else {
                    $('#rclone-status-text').html('<span class="text-warning">Rclone no detectado en el sistema.</span>');
                    $('#btnInstalarRclone').show();
                }
            }).fail(function() {
                $('#rclone-status-text').html('<span class="text-danger">Error al consultar el microservicio.</span>');
            });

        } else {
            btn.text('Activar Servicio Rcloner').removeClass('btn-danger btn-secondary').addClass('btn-success');
            $('#rclone-status-text').html('<span class="text-danger">Microservicio Rcloner apagado.</span>');
            $('#btnInstalarRclone').hide();
        }
    }).fail(function() {
        $('#btnToggleRcloneService').text('Error de comunicación').addClass('btn-secondary');
        $('#rclone-status-text').text('Error al verificar el estado del puerto 5001.');
    });
}

function toggleRcloneService() {
    const action = rcloneServiceActivo ? 'stop' : 'start';
    const btn = $('#btnToggleRcloneService');
    btn.prop('disabled', true).text('Procesando...');

    $.post('?controller=sacrej&action=api_toggle_server_rclone', { service_action: action }, function(res) {
        Swal.fire({
            icon: res.success ? 'success' : 'info',
            title: 'Servicio Rcloner',
            text: res.mensaje,
            timer: 2000,
            showConfirmButton: false
        });

        // Esperar un momento para que el servicio levante antes de verificar
        const delay = action === 'start' ? 4000 : 1000;
        setTimeout(() => {
            btn.prop('disabled', false);
            verificarRclone();
        }, delay);
    }, 'json').fail(() => {
        btn.prop('disabled', false);
        verificarRclone();
    });
}

function crearRemote(email) {
    if (!rcloneServiceActivo) {
        Swal.fire('Atención', 'Debe activar el microservicio Rcloner primero (Puerto 5001).', 'warning');
        return;
    }
    Swal.fire({
        title: 'Creando Remote...',
        text: 'Generando perfil de Rclone en el servidor...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    $.post('?controller=sacrej&action=api_crear_rclone_remote', { email: email }, function(res) {
        if (res.status === 'ok') {
            Swal.fire('Éxito', res.msg, 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', res.message || res.msg, 'error');
        }
    }, 'json').fail((xhr) => {
        let errorMsg = 'Error de comunicación con el microservicio.';
        if (xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
        Swal.fire('Error', errorMsg, 'error');
    });
}

function probarSubida(email) {
    if (!rcloneServiceActivo) {
        Swal.fire('Atención', 'Debe activar el microservicio Rcloner primero (Puerto 5001).', 'warning');
        return;
    }
    Swal.fire({
        title: 'Subiendo archivo...',
        text: 'Intentando transferir C:\\Users\\USUARIO\\Documents\\eureka2.txt',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    $.post('?controller=sacrej&action=api_test_rclone_upload', { email: email }, function(res) {
        if (res.status === 'ok') {
            Swal.fire('Éxito', res.msg, 'success');
        } else {
            Swal.fire('Error', res.message || res.msg, 'error');
        }
    }, 'json').fail((xhr) => {
        let errorMsg = 'Error de comunicación con el microservicio.';
        if (xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
        Swal.fire('Error', errorMsg, 'error');
    });
}

$('#btnInstalarRclone').click(function() {
    const btn = $(this);
    btn.prop('disabled', true).text('Instalando...');
    Swal.fire({ title: 'Instalando...', text: 'Descargando binarios de Rclone', didOpen: () => Swal.showLoading() });

    $.post('?controller=sacrej&action=api_instalar_rclone', function(res) {
        if (res.status === 'ok') {
            Swal.fire('Éxito', res.message, 'success');
            verificarRclone();
        } else {
            Swal.fire('Error', res.message, 'error');
            btn.prop('disabled', false).text('Instalar Rclone');
        }
    }, 'json').fail(() => {
        Swal.fire('Error', 'No se pudo contactar con el microservicio.', 'error');
        btn.prop('disabled', false).text('Instalar Rclone');
    });
});

$(document).ready(function() {
    verificarRclone();
    setInterval(verificarRclone, 10000); // Re-verificar cada 10 segundos
});

function toggleManualEmail(val) {
    if (val === 'manual') {
        $('#driveEmailManual').removeClass('d-none').focus();
    } else {
        $('#driveEmailManual').addClass('d-none').val('');
    }
}

$('#btnGuardarDrive').click(function() {
    let email = $('#driveEmailSelect').val();
    if (email === 'manual') email = $('#driveEmailManual').val().trim();
    const folderAddress = $('#folderAddress').val().trim();
    const fileInput = document.getElementById('jsonFile');

    if (!email) {
        Swal.fire('Atención', 'Debe especificar un correo electrónico.', 'warning');
        return;
    }

    let formData = new FormData();
    formData.append('email', email);
    formData.append('folderAddress', folderAddress);
    if (fileInput.files.length > 0) {
        formData.append('jsonFile', fileInput.files[0]);
    }

    Swal.fire({ title: 'Guardando...', didOpen: () => Swal.showLoading() });

    $.ajax({
        url: '?controller=sacrej&action=guardar_drive_cred',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(res) {
            if (res.status === 'ok') {
                Swal.fire('Éxito', res.msg, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.msg, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error de comunicación con el servidor.', 'error');
        }
    });
});

function eliminarDrive(email) {
    Swal.fire({
        title: '¿Eliminar credenciales?',
        text: "Se borrará la vinculación de Drive para " + email,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('?controller=sacrej&action=eliminar_drive_cred', { email: email }, function(res) {
                if (res.status === 'ok') {
                    Swal.fire('Eliminado', res.msg, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }, 'json');
        }
    });
}
</script>