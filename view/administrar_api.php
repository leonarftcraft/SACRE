<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<div class="container mt-4">
    <h4 class="text-center mb-4">Administrar API Key Gemini</h4>

    <div class="row">
        <!-- 📝 Formulario de Registro -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 fs-6">Registrar Nueva Key</h5>
                </div>
                <div class="card-body">
                    <form id="formApiKey" onsubmit="return false;">
                        <div class="mb-3">
                            <label class="form-label small">Correo Electrónico</label>
                            <input type="email" id="apiEmail" class="form-control" required placeholder="ejemplo@correo.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">API Key (Llave de Texto)</label>
                            <input type="text" id="apiKeyVal" class="form-control" required placeholder="Pegar llave aquí...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Clave de Correo</label>
                            <input type="password" id="emailPassVal" class="form-control" required placeholder="Clave de aplicación o contraseña...">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="button" id="btnGuardarApi" class="btn btn-success">Guardar</button>
                            <button type="button" id="btnLimpiarForm" class="btn btn-outline-secondary btn-sm" style="display:none;">Cancelar Edición / Limpiar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 📋 Tabla de Registros -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 fs-6">Llaves Registradas</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Correo</th>
                                    <th>Llave (Oculta)</th>
                                    <th>Clave Correo</th>
                                    <th>Fecha Registro</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tablaApiKeys">
                                <?php if (!empty($apiKeys)): ?>
                                    <?php foreach ($apiKeys as $k): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($k['email']) ?></td>
                                            <td>
                                                <div class="input-group input-group-sm" style="max-width: 200px;">
                                                    <input type="password" class="form-control" value="<?= htmlspecialchars($k['key']) ?>" readonly>
                                                    <button class="btn btn-outline-secondary btn-ver-key" type="button">👁️</button>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm" style="max-width: 200px;">
                                                    <input type="password" class="form-control" value="<?= htmlspecialchars($k['emailPass'] ?? '') ?>" readonly>
                                                    <button class="btn btn-outline-secondary btn-ver-key" type="button">👁️</button>
                                                </div>
                                            </td>
                                            <td class="small"><?= htmlspecialchars($k['fecha']) ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-info btn-editar-api" 
                                                        data-email="<?= htmlspecialchars($k['email']) ?>"
                                                        data-key="<?= htmlspecialchars($k['key']) ?>"
                                                        data-emailpass="<?= htmlspecialchars($k['emailPass'] ?? '') ?>">
                                                    Editar
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-eliminar-api" data-email="<?= htmlspecialchars($k['email']) ?>">
                                                    Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No hay llaves registradas.</td>
                                    </tr>
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
$(document).ready(function() {
    
    // 👁️ Ver/Ocultar llave en la tabla
    $(document).on('click', '.btn-ver-key', function() {
        let input = $(this).siblings('input');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).text('🔒');
        } else {
            input.attr('type', 'password');
            $(this).text('👁️');
        }
    });

    // ✏️ Cargar datos en el formulario para editar
    $(document).on('click', '.btn-editar-api', function() {
        const email = $(this).data('email');
        const key = $(this).data('key');
        const pass = $(this).data('emailpass');

        // Bloquear el correo para que no se pueda modificar la relación
        $('#apiEmail').val(email).prop('readonly', true);
        $('#apiKeyVal').val(key);
        $('#emailPassVal').val(pass);

        $('#btnGuardarApi').text('Actualizar').removeClass('btn-success').addClass('btn-primary');
        $('#btnLimpiarForm').show();
        
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    });

    // 🧹 Limpiar formulario / Cancelar edición
    $('#btnLimpiarForm').click(function() {
        $('#formApiKey')[0].reset();
        $('#apiEmail').prop('readonly', false);
        $('#btnGuardarApi').text('Guardar').removeClass('btn-primary').addClass('btn-success');
        $(this).hide();
    });

    // 💾 Guardar API Key
    $('#btnGuardarApi').click(function() {
        let email = $('#apiEmail').val().trim();
        let key = $('#apiKeyVal').val().trim();
        let emailPass = $('#emailPassVal').val().trim();

        if (!email || !key || !emailPass) {
            Swal.fire('Atención', 'Por favor, complete todos los campos.', 'warning');
            return;
        }

        let formData = new FormData();
        formData.append('email', email);
        formData.append('apiKey', key);
        formData.append('emailPass', emailPass);

        $.ajax({
            url: '?controller=sacrej&action=guardar_api_key',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                if (res.status === 'ok') {
                    Swal.fire('Guardado', res.msg, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
            }
        });
    });

    // 🗑️ Eliminar API Key
    $(document).on('click', '.btn-eliminar-api', function() {
        let email = $(this).data('email');
        
        Swal.fire({
            title: '¿Eliminar llave?',
            text: "Se perderá el acceso asociado a: " + email,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('?controller=sacrej&action=eliminar_api_key', { email: email }, function(res) {
                    if (res.status === 'ok') {
                        Swal.fire('Eliminado', res.msg, 'success').then(() => location.reload());
                    }
                }, 'json');
            }
        });
    });
});
</script>