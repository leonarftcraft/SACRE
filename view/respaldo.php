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
        <!-- 📦 Respaldo Local -->
        <div class="col-md-6">
            <div class="card shadow h-100 border-0">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h5 class="mb-0"><i class="bi bi-hdd-fill me-2"></i> Respaldo Local</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="card-text text-center mt-3">
                        Genera un archivo comprimido <strong>.7z</strong> de la base de datos e imágenes, guardándolo en una ruta específica del servidor local.
                    </p>
                    <div class="alert alert-info small mt-auto">
                        <i class="bi bi-info-circle-fill"></i> El archivo se almacenará directamente en el servidor.
                    </div>
                    <button onclick="iniciarRespaldoLocal()" class="btn btn-primary w-100 py-2 fw-bold">
                         Generar Respaldo
                    </button>
                </div>
            </div>
        </div>

        <!-- ☁️ Respaldo en Google Drive -->
        <div class="col-md-6">
            <div class="card shadow h-100 border-0">
                <div class="card-header bg-success text-white text-center py-3">
                    <h5 class="mb-0"><i class="bi bi-google me-2"></i> Respaldo en Google Drive</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="card-text text-center mt-3">
                        Crea un archivo comprimido <strong>.7z</strong> y lo divide en partes iguales según la cantidad de cuentas de Google Drive configuradas. Cada parte se sube automáticamente a una cuenta diferente usando rclone.
                        <ul class="text-start small text-muted mx-auto" style="max-width: 80%;">
                            <li>📂 Todas las imágenes de actas digitalizadas.</li>
                            <li>🗄️ Volcado SQL de la base de datos (sacrej).</li>
                            <li>🔀 División automática en partes iguales.</li>
                            <li>☁️ Subida distribuida a múltiples Google Drive.</li>
                        </ul>
                    </p>
                    <div class="alert alert-info small mt-auto">
                        <i class="bi bi-info-circle-fill"></i> Utiliza el microservicio rclone para dividir y distribuir el respaldo en la nube.
                    </div>
                    <button onclick="iniciarRespaldoGoogleDrive()" class="btn btn-success w-100 py-2 fw-bold">
                         Generar y Subir
                    </button>
                </div>
            </div>
        </div>
    </div>
    
<script>
let driveRemotes = []; // Para almacenar las configuraciones de Drive

/**
 * Inicia el proceso de respaldo local
 */
function iniciarRespaldoLocal() {
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
function explorarCarpetaServidor(path = '', pesoEstimado = null, targetAction = 'local') {
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
            if (result.isConfirmed) procesarGeneracion7z(result.value, pesoEstimado, targetAction);
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
function procesarGeneracion7z(ruta, pesoEstimado = '', targetAction = 'local', remoteName = null) {
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

    let postData = { save_path: ruta };
    let actionUrl = '?controller=sacrej&action=api_generar_respaldo_7z'; // Default local

    if (targetAction === 'drive') {
        actionUrl = '?controller=sacrej&action=api_respaldo_drive';
        postData.remote_name = remoteName;
    }

    $.post(actionUrl, postData, function(res) {
        Swal.close();
        if (res.status === 'ok') {
            let successHtml = `El archivo 7z se ha generado correctamente.<br><br>`;
            if (targetAction === 'local') {
                successHtml += `<small class="text-primary fw-bold">${res.full_path}</small><br>`;
            } else {
                successHtml += `<small class="text-primary fw-bold">Subido a Google Drive (${remoteName})</small><br>`;
            }
            successHtml += `<div class="mt-3"><span class="badge bg-info text-dark">Peso final: ${res.size}</span></div>`;

            Swal.fire({
                icon: 'success',
                title: targetAction === 'local' ? '¡Respaldo Guardado!' : '¡Respaldo en Drive Completado!',
                html: successHtml,
                confirmButtonText: 'Entendido'
            });
        } else {
            Swal.fire('Error', res.msg || 'No se pudo completar el respaldo.', 'error');
        }
    }, 'json').fail(function() {
        Swal.close();
        Swal.fire('Error', 'No se pudo conectar con el microservicio Rcloner. Verifique que esté activo (Puerto 5001).', 'error');
    });
}

/**
 * Inicia el proceso de respaldo a Google Drive
 */
function iniciarRespaldoGoogleDrive() {
    console.log('Función iniciarRespaldoGoogleDrive llamada');
    Swal.fire({
        title: '¿Iniciar respaldo en la nube?',
        text: "Se generará un paquete comprimido, se dividirá en partes iguales y cada parte se subirá a una cuenta de Google Drive usando rclone. Esto puede tardar unos minutos dependiendo del tamaño de las actas.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, iniciar sincronización',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sincronizando...',
                html: 'Comprimiendo, dividiendo en partes y subiendo a la nube.<br><b>Por favor, no cierre esta ventana.</b>',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.post('?controller=sacrej&action=api_respaldo_rclone_split', function(res) {
                Swal.close();
                if (res.status === 'ok') {
                    let resultsHtml = '<div class="text-start mt-2 small"><b>Detalles de subida:</b><ul class="mb-0">';
                    if (res.results && res.results.length > 0) {
                        res.results.forEach(result => resultsHtml += `<li>${result}</li>`);
                    }
                    resultsHtml += '</ul></div>';
                    if (res.invalid_remotes && res.invalid_remotes.length > 0) {
                        resultsHtml += '<div class="text-start mt-3 small text-warning"><b>Remotes omitidos:</b><ul class="mb-0">';
                        res.invalid_remotes.forEach(err => resultsHtml += `<li>${err}</li>`);
                        resultsHtml += '</ul></div>';
                    }
                    Swal.fire({
                        title: 'Respaldo Completado',
                        html: res.msg + resultsHtml,
                        icon: 'success'
                    });
                } else {
                    Swal.fire('Error', res.msg || 'No se pudo completar el respaldo.', 'error');
                }
            }, 'json').fail(function() {
                Swal.close();
                Swal.fire('Error', 'No se pudo conectar con el microservicio Rcloner. Verifique que esté activo (Puerto 5001).', 'error');
            });
        }
    });
}
</script>