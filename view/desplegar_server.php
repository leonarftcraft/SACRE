<div class="container mt-4">
    <h2 class="text-center mb-4 text-primary">Panel de Control del Servidor</h2>

    <!-- 🔹 SECCIÓN SUPERIOR: CONTROL DEL SERVER -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body text-center">
            <h5 class="card-title">Estado del Servidor</h5>
            
            <!-- Texto de estado -->
            <h3 id="textoEstado" class="fw-bold my-3 <?= $estado_server == '1' ? 'text-success' : 'text-danger' ?>">
                <?= $estado_server == '1' ? 'EL SERVER ESTÁ ACTIVO' : 'EL SERVER ESTÁ DESACTIVADO' ?>
            </h3>

            <!-- Botón Activar/Desactivar -->
            <button id="btnServer" class="btn btn-lg <?= $estado_server == '1' ? 'btn-danger' : 'btn-success' ?>" onclick="toggleServer()">
                <?= $estado_server == '1' ? 'Desactivar Server' : 'Activar Server' ?>
            </button>

            <!-- 🔹 SECCIÓN DE CONEXIÓN MÓVIL -->
            <div class="mt-4 pt-3 border-top">
                <h5 class="text-secondary">📡 Conexión para Móviles</h5>
                <p class="mb-2 small text-muted">Comparte este enlace para conectar los teléfonos (deben estar en la misma red WiFi):</p>
                
                <div class="input-group mb-3 mx-auto" style="max-width: 600px;">
                    <input type="text" class="form-control text-center fw-bold text-primary" id="inputUrlMovil" value="<?= $url_movil ?>" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copiarUrl()">
                        📋 Copiar
                    </button>
                    <button class="btn btn-success" type="button" onclick="enviarWhatsapp()">
                        📱 Enviar por WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 🔹 TABLA 1: DATOS COMPLETOS (Con Scroll y Exportar) -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📋 Datos de Bautizados</h5>
                    <button class="btn btn-light btn-sm fw-bold" onclick="exportarExcel()">
                        📥 Exportar a Excel
                    </button>
                </div>
                <div class="card-body p-0">
                    <!-- Contenedor con Scroll -->
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-striped table-hover mb-0" id="tablaCompleta">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Apellido</th>
                                    <th>Fecha Nac.</th>
                                    <th>Fecha Bautizo</th>
                                    <th>Libro</th>
                                    <th>Folio</th>
                                    <th>Detalles</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Reiniciamos el puntero si se usó antes, aunque aquí es nuevo
                                if ($listaBautizados):
                                    foreach ($listaBautizados as $row): 
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['IdInd']) ?></td>
                                    <td><?= htmlspecialchars($row['NomInd']) ?></td>
                                    <td><?= htmlspecialchars($row['ApeInd']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['FecNacInd'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['FechCel'])) ?></td>
                                    <td><?= htmlspecialchars($row['NumLib']) ?></td>
                                    <td><?= htmlspecialchars($row['NumFol']) ?></td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-sm btn-info text-white btnDetalle" data-idcel="<?= $row['IdCel'] ?>">
                                            Ver
                                        </button>
                                        <?php if (!empty($row['UrlArchivo'])): ?>
                                            <a href="<?= htmlspecialchars($row['UrlArchivo']) ?>" target="_blank" class="btn btn-sm btn-success ms-1" title="Ver Imagen">
                                                📷
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php 
                                    endforeach; 
                                endif; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔹 TABLA 2: SOLO NOMBRES -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">👤 Lista de clientes Conectados</h5>
                </div>
                <div class="card-body p-0">
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Nombre Completo</th>
                                </tr>
                            </thead>
                            <tbody id="tablaClientesConectados">
                                <tr><td class="text-muted text-center">Esperando conexiones...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 🔹 TABLA 3: BAUTIZOS RECIBIDOS (PENDIENTES) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">⏳ Bautizos Recibidos (Desde Móviles)</h5>
                    <button class="btn btn-success btn-sm fw-bold" onclick="guardarTodosPendientes()">
                        💾 Guardar Todo
                    </button>
                    <span class="badge bg-dark text-warning fs-6" id="contadorPendientes">0</span>
                </div>
                <div class="card-body p-0">
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Enviado Por</th>
                                    <th>Bautizado</th>
                                    <th>Fecha Nac.</th>
                                    <th>Libro</th>
                                    <th>Folio</th>
                                    <th>Imagen</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaPendientes">
                                <tr><td colspan="6" class="text-center text-muted">Esperando datos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 🔹 MODAL EDITAR PENDIENTE -->
<div class="modal fade" id="modalEditarPendiente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Editar Registro Pendiente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarPendiente">
                    <input type="hidden" id="editIndex" name="index">
                    
                    <!-- Encabezado -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-4"><label class="form-label small">N° Celebración</label><input type="number" id="editIdCel" name="IdCel" class="form-control form-control-sm"></div>
                        <div class="col-md-4"><label class="form-label small">Libro</label><input type="number" id="editNumLib" name="NumLib" class="form-control form-control-sm"></div>
                        <div class="col-md-4"><label class="form-label small">Folio</label><input type="number" id="editNumFol" name="NumFol" class="form-control form-control-sm"></div>
                    </div>

                    <!-- Bautizado -->
                    <h6 class="text-secondary border-bottom">Datos del Bautizado</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6"><label class="form-label small">Nombre</label><input type="text" id="editNomInd" name="NomInd" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label small">Apellido</label><input type="text" id="editApeInd" name="ApeInd" class="form-control form-control-sm"></div>
                        <div class="col-md-4"><label class="form-label small">Fecha Nac.</label><input type="date" id="editFecNacInd" name="FecNacInd" class="form-control form-control-sm"></div>
                        <div class="col-md-4"><label class="form-label small">Lugar Nac.</label><input type="text" id="editLugNacInd" name="LugNacInd" class="form-control form-control-sm"></div>
                        <div class="col-md-4"><label class="form-label small">Filiación</label>
                            <select id="editFilInd" name="FilInd" class="form-select form-select-sm">
                                <option value="1">Reconocido</option><option value="2">Legítimo</option><option value="3">Natural</option><option value="4">Ilegítimo</option><option value="0">No reconocido</option>
                            </select>
                        </div>
                    </div>

                    <!-- Padres -->
                    <h6 class="text-secondary border-bottom">Padres</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6"><label class="form-label small">Madre (Nom)</label><input type="text" id="editNomMad" name="NomMad" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label small">Madre (Ape)</label><input type="text" id="editApeMad" name="ApeMad" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label small">Padre (Nom)</label><input type="text" id="editNomPad" name="NomPad" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label small">Padre (Ape)</label><input type="text" id="editApePad" name="ApePad" class="form-control form-control-sm"></div>
                    </div>

                    <!-- Celebración -->
                    <h6 class="text-secondary border-bottom">Celebración</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-md-4"><label class="form-label small">Fecha Bautizo</label><input type="date" id="editFechCel" name="FechCel" class="form-control form-control-sm"></div>
                        <div class="col-md-4"><label class="form-label small">Lugar</label><input type="text" id="editLugar" name="Lugar" class="form-control form-control-sm"></div>
                        <div class="col-md-4"><label class="form-label small">Ministro</label>
                            <select id="editIdMin" name="IdMin" class="form-select form-select-sm">
                                <option value="">Seleccione...</option>
                                <?php 
                                if (isset($ministros) && $ministros) {
                                    $ministros->data_seek(0);
                                    while ($m = $ministros->fetch_assoc()) {
                                        echo "<option value='{$m['IdMinCel']}'>" . htmlspecialchars($m['Nom'] . ' ' . $m['Ape']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Padrinos -->
                    <h6 class="text-secondary border-bottom">Padrinos</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6"><label class="form-label small">Padrino (Nom)</label><input type="text" id="editPad1Nom" name="Pad1Nom" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label small">Padrino (Ape)</label><input type="text" id="editPad1Ape" name="Pad1Ape" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label small">Madrina (Nom)</label><input type="text" id="editPad2Nom" name="Pad2Nom" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label small">Madrina (Ape)</label><input type="text" id="editPad2Ape" name="Pad2Ape" class="form-control form-control-sm"></div>
                    </div>

                    <!-- Otros -->
                    <div class="row g-2">
                        <div class="col-md-8"><label class="form-label small">Observaciones</label><textarea id="editNotMar" name="NotMar" class="form-control form-control-sm"></textarea></div>
                        <div class="col-md-4"><label class="form-label small">Reg. Civil</label><input type="text" id="editRegCiv" name="RegCiv" class="form-control form-control-sm"></div>
                    </div>

                    <!-- Imagen -->
                    <div class="mt-3 p-2 bg-light border rounded">
                        <label class="form-label small fw-bold">Imagen Asociada:</label>
                        <div id="linkImagenContainer"></div>
                        <input type="hidden" id="editRutaImagen" name="RutaImagen">
                    </div>
                    
                    <!-- Campos ocultos necesarios -->
                    <input type="hidden" id="editIdInd" name="IdInd">
                    <input type="hidden" id="editSexInd" name="SexInd">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarEdicionPendiente()">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- 🔹 MODAL DETALLES -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Detalles del Bautizado</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nombre:</strong> <span id="dNombre"></span></p>
                <p><strong>Apellido:</strong> <span id="dApellido"></span></p>
                <p><strong>Fecha Nacimiento:</strong> <span id="dFecNac"></span></p>
                <p><strong>Lugar Nacimiento:</strong> <span id="dLugNac"></span></p>
                <p><strong>Filiación:</strong> <span id="dFiliacion"></span></p>
            </div>
            <div class="col-md-6">
                <p><strong>Fecha Bautizo:</strong> <span id="dFecBaut"></span></p>
                <p><strong>Lugar Bautizo:</strong> <span id="dLugBaut"></span></p>
                <p><strong>Libro:</strong> <span id="dLibro"></span> <strong>Folio:</strong> <span id="dFolio"></span></p>
                <p><strong>Ministro:</strong> <span id="dMinistro"></span></p>
                <p><strong>Tipo:</strong> <span id="dTipo"></span></p>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12">
                <p><strong>Padres:</strong> <span id="dPadres"></span></p>
                <p><strong>Padrinos:</strong> <span id="dPadrinos"></span></p>
                <p><strong>Registro Civil:</strong> <span id="dRegCiv"></span></p>
                <p><strong>Observaciones:</strong> <span id="dObs"></span></p>
                <div id="dImagenContainer" class="mt-3"></div>
                <div id="dDigitalizador" class="small text-muted fst-italic mt-1"></div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// 🔹 Lógica para Activar/Desactivar Server
let estadoActual = <?= (int)$estado_server ?>; // 0 o 1

function toggleServer() {
    const nuevoEstado = estadoActual == 1 ? 0 : 1;

    $.post('?controller=sacrej&action=cambiar_estado_server', { estado: nuevoEstado }, function(res) {
        if (res.success) {
            estadoActual = res.estado;
            actualizarVisuales(estadoActual);
            Swal.fire('Actualizado', res.mensaje, 'success');
        }
    }, 'json').fail(function() {
        Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
    });
}

// 🔹 Actualizar UI visualmente (Función auxiliar)
function actualizarVisuales(estado) {
    const texto = $('#textoEstado');
    const boton = $('#btnServer');

    if (estado == 1) {
        texto.text('EL SERVER ESTÁ ACTIVO').removeClass('text-danger').addClass('text-success');
        boton.text('Desactivar Server').removeClass('btn-success').addClass('btn-danger');
    } else {
        texto.text('EL SERVER ESTÁ DESACTIVADO').removeClass('text-success').addClass('text-danger');
        boton.text('Activar Server').removeClass('btn-danger').addClass('btn-success');
    }
}

// 🔹 Función para desactivar silenciosamente (con callback opcional)
function desactivarServer(callback) {
    $.post('?controller=sacrej&action=cambiar_estado_server', { estado: 0 }, function(res) {
        if (res.success) {
            estadoActual = 0;
            actualizarVisuales(0);
            if (callback) callback();
        }
    }, 'json');
}

// 🔹 Lógica para Exportar a Excel (Genera un archivo .xls simple basado en HTML)
function exportarExcel() {
    Swal.fire({
        title: 'Seguridad',
        text: 'Ingrese su contraseña de administrador para exportar los datos:',
        input: 'password',
        inputAttributes: {
            autocapitalize: 'off',
            placeholder: 'Contraseña'
        },
        showCancelButton: true,
        confirmButtonText: 'Validar y Exportar',
        cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: (password) => {
            return $.post('?controller=sacrej&action=validar_clave_exportacion', { password: password })
                .then(response => {
                    if (!response.success) {
                        throw new Error(response.msg || 'Contraseña incorrecta');
                    }
                    return response;
                })
                .catch(error => {
                    Swal.showValidationMessage(`Error: ${error.message}`);
                });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            // 🔹 Redirigir al controlador para descargar el reporte completo desde la BD
            window.location.href = '?controller=sacrej&action=descargar_reporte_bautizos_completo';
            
            Swal.fire('Éxito', 'Exportación iniciada correctamente.', 'success');
        }
    });
}

// 🔹 Funciones para compartir URL
function copiarUrl() {
    var copyText = document.getElementById("inputUrlMovil");
    copyText.select();
    copyText.setSelectionRange(0, 99999); /* Para móviles */
    
    navigator.clipboard.writeText(copyText.value).then(() => {
        Swal.fire('Copiado', 'Enlace copiado al portapapeles', 'success');
    });
}

function enviarWhatsapp() {
    var url = document.getElementById("inputUrlMovil").value;
    var texto = url;
    window.open("https://wa.me/?text=" + encodeURIComponent(texto), '_blank');
}

// 🔹 Lógica para actualizar lista de clientes conectados (Polling)
function actualizarClientes() {
    $.get('?controller=sacrej&action=api_obtener_clientes', function(clientes) {
        let html = '';
        if (clientes && clientes.length > 0) {
            clientes.forEach(c => {
                html += `<tr><td>🟢 ${c}</td></tr>`;
            });
        } else {
            html = '<tr><td class="text-muted text-center">Esperando conexiones...</td></tr>';
        }
        $('#tablaClientesConectados').html(html);
    }, 'json');
}

// Actualizar cada 2 segundos
setInterval(actualizarClientes, 2000);
actualizarClientes(); // Primera carga inmediata

// 🔹 Lógica para actualizar Bautizos Pendientes (Polling)
let datosPendientes = [];

function actualizarPendientes() {
    $.get('?controller=sacrej&action=api_obtener_bautizos_temporales', function(data) {
        datosPendientes = data || [];
        $('#contadorPendientes').text(datosPendientes.length);
        let html = '';
        if (datosPendientes.length > 0) {
            // Mostrar los más recientes primero (iterar al revés)
            for (let i = datosPendientes.length - 1; i >= 0; i--) {
                let row = datosPendientes[i];
                let nombre = (row.NomInd || '') + ' ' + (row.ApeInd || '');
                let fecha = row.FecNacInd || '';
                
                let btnImagen = '';
                if (row.RutaImagen) {
                    btnImagen = `<a href="${row.RutaImagen}" target="_blank" class="btn btn-sm btn-outline-primary" title="Ver Imagen">📷</a>`;
                }

                html += `<tr>
                    <td>📱 ${row.usuario_envio || 'Anónimo'}</td>
                    <td>${nombre}</td>
                    <td>${fecha}</td>
                    <td>${row.NumLib || ''}</td>
                    <td>${row.NumFol || ''}</td>
                    <td>${btnImagen}</td>
                    <td>
                        <button class="btn btn-sm btn-success" onclick="guardarPendiente(${i})" title="Guardar en BD">💾</button>
                        <button class="btn btn-sm btn-warning" onclick="editarPendiente(${i})" title="Editar">✏️</button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarPendiente(${i})" title="Eliminar">🗑️</button>
                    </td>
                </tr>`;
            }
        } else {
            html = '<tr><td colspan="7" class="text-center text-muted">No hay registros pendientes.</td></tr>';
        }
        $('#tablaPendientes').html(html);
    }, 'json');
}
setInterval(actualizarPendientes, 3000);
actualizarPendientes();

function guardarPendiente(index) {
    Swal.fire({
        title: '¿Guardar este registro?',
        text: "El bautizo se registrará permanentemente en la base de datos.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Ejecutar Guardado Real
            Swal.fire({ title: 'Guardando...', didOpen: () => Swal.showLoading() });
            $.post('?controller=sacrej&action=api_aprobar_bautizo_temporal', { index: index }, function(saveRes) {
                Swal.close();
                if (saveRes.status === 'ok') {
                    Swal.fire('Guardado', saveRes.msg, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', saveRes.msg, 'error');
                }
            }, 'json');
        }
    });
}

function guardarTodosPendientes() {
    Swal.fire({ title: 'Procesando...', didOpen: () => Swal.showLoading() });
    $.post('?controller=sacrej&action=api_aprobar_todos_bautizos_temporales', function(res) {
        Swal.close();
        Swal.fire('Proceso finalizado', `Guardados: ${res.guardados}, Errores: ${res.errores}`, 'info')
            .then(() => location.reload());
    }, 'json');
}

function eliminarPendiente(index) {
    Swal.fire({
        title: '¿Eliminar registro?',
        text: "Se eliminará este registro, la imagen asociada y cualquier otro registro vinculado a esa misma captura. Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('?controller=sacrej&action=api_eliminar_bautizo_temporal', { index: index }, function(res) {
                if (res.status === 'ok') {
                    Swal.fire('Eliminado', res.msg, 'success');
                    actualizarPendientes();
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }, 'json');
        }
    });
}

function editarPendiente(index) {
    let d = datosPendientes[index];
    if (!d) return;

    // 🔹 Cargar lista actualizada de ministros vía AJAX
    $.get('?controller=sacrej&action=api_obtener_ministros', function(ministros) {
        let select = $('#editIdMin');
        select.empty();
        select.append('<option value="">Seleccione...</option>');
        
        if (ministros && ministros.length > 0) {
            ministros.forEach(m => {
                select.append(`<option value="${m.IdMinCel}">${m.Nom} ${m.Ape}</option>`);
            });
        }

        // Llenar el formulario del modal
        $('#editIndex').val(index);
        $('#editIdCel').val(d.IdCel);
        $('#editNumLib').val(d.NumLib);
        $('#editNumFol').val(d.NumFol);
        $('#editNomInd').val(d.NomInd);
        $('#editApeInd').val(d.ApeInd);
        $('#editFecNacInd').val(d.FecNacInd);
        $('#editLugNacInd').val(d.LugNacInd);
        $('#editFilInd').val(d.FilInd);
        $('#editNomMad').val(d.NomMad);
        $('#editApeMad').val(d.ApeMad);
        $('#editNomPad').val(d.NomPad);
        $('#editApePad').val(d.ApePad);
        $('#editFechCel').val(d.FechCel);
        $('#editLugar').val(d.Lugar);
        
        // Seleccionar el ministro (ahora que la lista está actualizada)
        $('#editIdMin').val(d.IdMin);
        
        $('#editPad1Nom').val(d.Pad1Nom);
        $('#editPad1Ape').val(d.Pad1Ape);
        $('#editPad2Nom').val(d.Pad2Nom);
        $('#editPad2Ape').val(d.Pad2Ape);
        $('#editNotMar').val(d.NotMar);
        $('#editRegCiv').val(d.RegCiv);
        $('#editIdInd').val(d.IdInd);
        $('#editSexInd').val(d.SexInd);
        $('#editRutaImagen').val(d.RutaImagen || '');

        // Mostrar enlace a imagen si existe
        if (d.RutaImagen) {
            $('#linkImagenContainer').html(`<a href="${d.RutaImagen}" target="_blank" class="btn btn-sm btn-info text-white">👁️ Ver Imagen Original</a>`);
        } else {
            $('#linkImagenContainer').html('<span class="text-muted small">Sin imagen</span>');
        }

        new bootstrap.Modal(document.getElementById('modalEditarPendiente')).show();
    }, 'json').fail(function() {
        Swal.fire('Error', 'No se pudo cargar la lista de ministros.', 'error');
    });
}

function guardarEdicionPendiente() {
    const formData = $('#formEditarPendiente').serialize();
    
    $.post('?controller=sacrej&action=api_editar_bautizo_temporal', formData, function(res) {
        if (res.status === 'ok') {
            Swal.fire('Guardado', res.msg, 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalEditarPendiente')).hide();
            actualizarPendientes();
        } else {
            Swal.fire('Error', res.msg, 'error');
        }
    }, 'json');
}

// 🔹 Lógica para Ver Detalles
$(document).on('click', '.btnDetalle', function() {
    let idCel = $(this).data('idcel');
    
    // Mostrar cargando o limpiar modal previo
    $('#dNombre').text('Cargando...');
    
    $.post('?controller=sacrej&action=detalle_celebracion', { idCel: idCel }, function(res) {
        if(res.success) {
            let d = res.data;
            $('#dNombre').text(d.nombre);
            $('#dApellido').text(d.apellido);
            $('#dFecNac').text(d.fec_nac);
            $('#dLugNac').text(d.lugar_nac);
            
            let fil = d.filiacion;
            if(fil == 1) fil = 'Reconocido';
            else if(fil == 2) fil = 'Legítimo';
            else if(fil == 3) fil = 'Natural';
            $('#dFiliacion').text(fil);
            
            $('#dFecBaut').text(d.fecha_bautizo);
            $('#dLugBaut').text(d.lugar_bautizo);
            $('#dLibro').text(d.num_libro);
            $('#dFolio').text(d.num_folio);
            $('#dMinistro').text(d.ministro);
            $('#dTipo').text(d.tipo_celebracion);
            
            $('#dPadres').text(d.padres);
            $('#dPadrinos').text(d.padrinos);
            $('#dRegCiv').text(d.registro_civil);
            $('#dObs').text(d.observaciones);
            
            // 📷 Mostrar imagen si existe
            $('#dDigitalizador').empty();
            if (d.imagen) {
                $('#dImagenContainer').html(`<a href="${d.imagen}" target="_blank" class="btn btn-sm btn-success w-100">📷 Ver Imagen del Acta</a>`);
                if (d.digitalizador) {
                    $('#dDigitalizador').text("Digitalizado por: " + d.digitalizador);
                }
            } else {
                $('#dImagenContainer').html('<span class="text-muted small fst-italic">No tiene imagen asociada.</span>');
            }
            
            new bootstrap.Modal(document.getElementById('modalDetalle')).show();
        } else {
            Swal.fire('Error', 'No se pudieron cargar los detalles', 'error');
        }
    }, 'json').fail(function() {
        Swal.fire('Error', 'Error de conexión', 'error');
    });
});

// 🔹 Lógica de Seguridad y Navegación
$(document).ready(function() {
    // Desactivar servidor automáticamente solo al navegar a otra opción del menú
    $('.navbar-nav a, .dropdown-item').on('click', function() {
        const href = $(this).attr('href');
        // Si es un enlace válido y NO es volver a esta misma vista
        if (href && href !== '#' && !href.includes('action=vista_desplegar_server')) {
             var data = new URLSearchParams();
             data.append('estado', '0');
             navigator.sendBeacon("?controller=sacrej&action=cambiar_estado_server", data);
        }
    });
});
</script>