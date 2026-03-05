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
                                    <td>
                                        <button class="btn btn-sm btn-info text-white btnDetalle" data-idcel="<?= $row['IdCel'] ?>">
                                            Ver
                                        </button>
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
    var table = document.getElementById("tablaCompleta");
    var html = table.outerHTML;
    
    // Crear un blob con el contenido HTML de la tabla
    var url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
    
    // Crear enlace temporal para descarga
    var downloadLink = document.createElement("a");
    document.body.appendChild(downloadLink);
    downloadLink.href = url;
    downloadLink.download = "reporte_bautizados.xls";
    downloadLink.click();
    document.body.removeChild(downloadLink);
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
    // 1. Por defecto al iniciar desactivada
    if (estadoActual === 1) {
        desactivarServer();
    }
});
</script>