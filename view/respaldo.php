<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!-- FontAwesome para los iconos de carpetas -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Estética moderna estilo Windows 11 / Drive */
    .explorer-container {
        max-height: 350px;
        overflow-y: auto;
        overflow-x: hidden;
        border-radius: 8px;
        background: #fff;
        scrollbar-width: thin;
    }
    .folder-item {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        margin: 2px 5px;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.2s, border 0.2s;
        border: 1px solid transparent;
        user-select: none;
    }
    .folder-item:hover {
        background-color: #f3f4f6;
    }
    .folder-item.selected {
        background-color: #e7f1ff !important;
        border: 1px solid #b6d4fe;
    }
    .folder-icon {
        font-size: 1.4rem;
        margin-right: 12px;
        color: #ffca28; /* Amarillo carpeta */
        background: linear-gradient(180deg, #ffeb3b 0%, #fbc02d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(0 1px 1px rgba(0,0,0,0.1));
    }
    .breadcrumb-item a {
        text-decoration: none;
        color: #0d6efd;
        font-weight: 500;
    }
    .breadcrumb-item.active { color: #6c757d; }
</style>

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
                        Esta opción generará y descargará un archivo comprimido (<strong>.7z</strong>) que contiene:
                        <ul class="text-start small text-muted mx-auto" style="max-width: 80%;">
                            <li>📂 Todas las imágenes de actas digitalizadas.</li>
                            <li>🗄️ Volcado SQL de la base de datos (sacrej).</li>
                        </ul>
                    </p>
                    <div class="alert alert-info small mt-auto">
                        <i class="bi bi-info-circle-fill"></i> Guarde este archivo en una ubicación segura designada por la administración.
                    </div>
                    <button onclick="solicitarRespaldo()" class="btn btn-primary w-100 py-2 fw-bold">
                         Generar Respaldo
                    </button>
                </div>
            </div>
        </div>
    </div>
    
<script>
/**
 * Inicia el proceso de respaldo delegando la compresión al microservicio Python
 */
function solicitarRespaldo() {
    Swal.fire({
        title: 'Calculando tamaño...',
        text: 'Analizando imágenes y base de datos.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.getJSON('?controller=sacrej&action=api_obtener_tamano_pre_respaldo', function(res) {
        Swal.close();
        if (res.status === 'ok') {
            explorarCarpetaServidor('ROOT', res.formatted);
        } else {
            explorarCarpetaServidor('ROOT');
        }
    }).fail(() => {
        Swal.close();
        explorarCarpetaServidor('ROOT');
    });
}

/**
 * Abre un explorador de archivos modal para seleccionar la ruta de destino
 */
function explorarCarpetaServidor(path = '', pesoEstimado = null) {
    // Mantener el peso entre navegaciones
    const sizeHeader = pesoEstimado ? `<span class="badge bg-warning text-dark float-end" style="font-size: 0.7rem;">Peso estimado: ${pesoEstimado}</span>` : '';

    $.post('?controller=sacrej&action=api_explorar_directorios', { path: path }, function (res) {
        if (res.status === 'error') {
            Swal.fire('Error', res.msg, 'error');
            return;
        }

        // 1. Generar Breadcrumbs (Migas de Pan)
        let breadcrumbs = `<nav aria-label="breadcrumb"><ol class="breadcrumb bg-light p-2 rounded mb-3 small">`;
        breadcrumbs += `<li class="breadcrumb-item"><a href="#" onclick="explorarCarpetaServidor('ROOT')"><i class="fa-solid fa-display me-1"></i>Mi Equipo</a></li>`;

        if (!res.is_root) {
            const separator = res.current.includes('/') ? '/' : '\\';
            const parts = res.current.split(separator).filter(p => p !== "");
            let currentAcc = "";
            parts.forEach((p, i) => {
                currentAcc += (i === 0 && p.includes(':')) ? p : separator + p;
                const escaped = currentAcc.replace(/\\/g, '\\\\');
                if (i === parts.length - 1) {
                    breadcrumbs += `<li class="breadcrumb-item active text-truncate" style="max-width:150px;">${p}</li>`;
                } else {
                    breadcrumbs += `<li class="breadcrumb-item"><a href="#" onclick="explorarCarpetaServidor('${escaped}')">${p}</a></li>`;
                }
            });
        }
        breadcrumbs += `</ol></nav>`;

        // 2. Generar Listado
        let listado = "";
        if (res.is_root) {
            listado = res.locations.map(loc => {
                let escaped = loc.path.replace(/\\/g, '\\\\');
                let iconClass = loc.icon === 'drive' ? 'fa-hard-drive drive' : 'fa-folder';
                return `
                    <div class="folder-item" data-path="${escaped}" onclick="seleccionarCarpeta(this)" ondblclick="explorarCarpetaServidor('${escaped}')">
                        <i class="fa-solid ${iconClass} folder-icon"></i>
                        <span class="text-truncate">${loc.name}</span>
                    </div>`;
            }).join('');
        } else {
            listado = res.items.map(item => {
                let escaped = item.path.replace(/\\/g, '\\\\');
                let icon = item.is_dir ? 'fa-folder' : 'fa-file-zipper';
                let sizeSpan = item.is_dir ? '' : `<small class="text-muted ms-auto pe-2">${item.size}</small>`;
                let clickAction = item.is_dir ? `onclick="seleccionarCarpeta(this)"` : `onclick="seleccionarArchivo(this, '${item.size}')"`;
                let dblClickAction = item.is_dir ? `ondblclick="explorarCarpetaServidor('${escaped}')"` : `ondblclick="descargarArchivo('${escaped}')"`;
                
                return `
                    <div class="folder-item d-flex align-items-center" data-path="${escaped}" ${clickAction} ${dblClickAction}>
                        <i class="fa-solid ${icon} folder-icon ${item.is_dir ? '' : 'text-secondary'}"></i>
                        <span class="text-truncate">${item.name}</span>
                        ${sizeSpan}
                    </div>`;
            }).join('');
        }

        Swal.fire({
            title: `<div class="clearfix"><i class="fa-solid fa-folder-open text-primary me-2"></i>Seleccionar Destino ${sizeHeader}</div>`,
            html: `
                <div class="text-start">
                    ${breadcrumbs}
                    <div class="mb-2">
                        <input type="text" id="busquedaCarpeta" class="form-control form-control-sm" placeholder="🔍 Buscar carpeta en este nivel...">
                    </div>
                    <div class="explorer-container border shadow-sm">
                        <div id="listaCarpetas">
                            ${listado || '<div class="p-4 text-center text-muted small"><i class="fa-solid fa-ghost d-block mb-2 fs-4"></i>No hay subcarpetas</div>'}
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="small fw-bold text-muted mb-1">Ruta seleccionada:</label>
                        <input type="text" id="pathDestino" class="form-control form-control-sm bg-light" value="${res.current}" readonly>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Generar aquí',
            cancelButtonText: 'Cancelar',
            width: '550px',
            didOpen: () => {
                // Si estamos en Mi Equipo, el botón confirmar se deshabilita hasta elegir una ruta física o carpeta de usuario
                if (res.is_root) Swal.getConfirmButton().setAttribute('disabled', 'true');

                // Filtrado en tiempo real
                $('#busquedaCarpeta').on('input', function() {
                    const query = $(this).val().toLowerCase();
                    $('.folder-item').each(function() {
                        const name = $(this).find('span').text().toLowerCase();
                        $(this).toggle(name.includes(query));
                    });
                });
            },
            preConfirm: () => {
                return document.getElementById('pathDestino').value;
            }
        }).then((result) => {
            if (result.isConfirmed) procesarGeneracion7z(result.value, pesoEstimado);
        });
    }, 'json');
}

/**
 * Maneja el clic en un archivo para resaltarlo y mostrar su peso en el botón
 */
function seleccionarArchivo(element, size) {
    $('.folder-item').removeClass('selected');
    $(element).addClass('selected');
    
    const ruta = $(element).data('path');
    $('#pathDestino').val(ruta);
    
    const btn = Swal.getConfirmButton();
    btn.innerHTML = `Descargar (${size})`;
    btn.classList.replace('btn-primary', 'btn-success');
}

/**
 * Descarga un archivo existente directamente
 */
function descargarArchivo(ruta) {
    window.location.href = '?controller=sacrej&action=descargar_archivo_respaldo&file=' + encodeURIComponent(ruta);
}

/**
 * Maneja el clic simple para resaltar y actualizar el input de ruta
 */
function seleccionarCarpeta(element) {
    $('.folder-item').removeClass('selected');
    $(element).addClass('selected');
    
    const nuevaRuta = $(element).data('path');
    $('#pathDestino').val(nuevaRuta);
    
    // Efecto visual en el botón de confirmar
    const btn = Swal.getConfirmButton();
    btn.innerHTML = 'Generar aquí';
    btn.classList.replace('btn-primary', 'btn-success');
}

/**
 * Envía la ruta seleccionada al controlador PHP para que Python genere el archivo
 */
function procesarGeneracion7z(ruta, pesoEstimado = '') {
    Swal.fire({
        title: 'Generando Respaldo...',
        html: `
            <div class="p-3 text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p class="mb-0 fw-bold">Comprimiendo aproximadamente ${pesoEstimado}...</p>
                <small class="text-muted">Enviando solicitud al microservicio de Python.</small>
            </div>
        `,
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.post('?controller=sacrej&action=api_generar_respaldo_7z', { save_path: ruta }, function(res) {
        Swal.close();
        if (res.status === 'ok') {
            Swal.fire({
                icon: 'success',
                title: '¡Respaldo Guardado!',
                html: `El archivo 7z se ha generado correctamente en el servidor:<br><br>
                       <small class="text-primary fw-bold">${res.full_path}</small><br>
                       <div class="mt-3"><span class="badge bg-info text-dark">Peso final: ${res.size}</span></div>`,
                confirmButtonText: 'Entendido'
            });
        } else {
            Swal.fire('Error', res.msg || 'No se pudo completar el respaldo.', 'error');
        }
    }, 'json').fail(function() {
        Swal.close();
        Swal.fire('Error', 'No se pudo conectar con el microservicio. Verifique el puerto 5001.', 'error');
    });
}

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