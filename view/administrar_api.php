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
                            <label class="form-label small">API Key (Llave)</label>
                            <input type="text" id="apiKeyVal" class="form-control" required placeholder="Pegar llave aquí...">
                        </div>
                        <button type="button" id="btnGuardarApi" class="btn btn-success w-100">Guardar</button>
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
                                            <td class="small"><?= htmlspecialchars($k['fecha']) ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-danger btn-eliminar-api" data-email="<?= htmlspecialchars($k['email']) ?>">
                                                    Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No hay llaves registradas.</td>
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

    // 💾 Guardar API Key
    $('#btnGuardarApi').click(function() {
        let email = $('#apiEmail').val().trim();
        let key = $('#apiKeyVal').val().trim();

        if (!email || !key) {
            Swal.fire('Atención', 'Complete ambos campos', 'warning');
            return;
        }

        $.post('?controller=sacrej&action=guardar_api_key', { email: email, apiKey: key }, function(res) {
            if (res.status === 'ok') {
                Swal.fire('Guardado', res.msg, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.msg, 'error');
            }
        }, 'json');
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