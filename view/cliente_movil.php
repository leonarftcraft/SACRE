<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Escáner SACRA</title>
    <link href="view/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .mobile-card { width: 100%; max-width: 400px; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; }
        .hidden { display: none !important; }
        .btn-big { padding: 15px; font-size: 1.2rem; width: 100%; border-radius: 12px; margin-top: 15px; }
        #resultado { text-align: left; background: #f8f9fa; padding: 15px; border-radius: 10px; margin-top: 20px; font-size: 0.9rem; max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; }
        .loader { border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <!-- PANTALLA 1: LOGIN -->
    <div id="screen-login" class="mobile-card">
        <img src="view/images/logo.png" alt="Logo" style="height: 80px; margin-bottom: 20px;">
        <h3 class="mb-3">Bienvenido</h3>
        <p class="text-muted mb-4">Ingresa tu nombre para conectarte al servidor.</p>
        
        <input type="text" id="inputNombre" class="form-control form-control-lg text-center" placeholder="Tu Nombre">
        <button class="btn btn-primary btn-big" onclick="conectarUsuario()">Entrar</button>
    </div>

    <!-- PANTALLA DE ESPERA DE AUTORIZACIÓN -->
    <div id="screen-wait" class="mobile-card hidden">
        <div class="loader"></div>
        <h4 class="mt-3">Esperando Autorización</h4>
        <p class="text-muted small">El administrador debe permitir tu conexión desde el panel central para poder usar el sistema.</p>
        <button class="btn btn-outline-danger mt-4" onclick="desconectar()">Cancelar / Salir</button>
    </div>

    <!-- PANTALLA 2: CÁMARA -->
    <div id="screen-camera" class="mobile-card hidden">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="text-start">
                <h5 class="m-0 text-primary" id="userDisplay">Usuario</h5>
                <small class="text-muted d-block" id="apiDisplay" style="font-size: 0.65rem;"></small>
                <span id="ia-quota-badge" class="badge hidden" style="font-size: 0.6rem;"></span>
            </div>
            <button class="btn btn-sm btn-outline-danger" onclick="desconectar()">Salir</button>
        </div>
        <hr>
        
        <div class="py-4">
            <h4>Escanear Documento</h4>
            <p class="text-muted small">Toma una foto clara del documento para extraer el texto.</p>
            
            <!-- 🤖 Selector de Modelo -->
            <div class="mt-2 text-start">
                <label class="form-label small fw-bold mb-1">Modelo de Análisis:</label>
                <select id="modelSelector" class="form-select form-select-sm">
                    <option value="gemini-3.1-flash-lite-preview" selected>gemini-3.1-flash-lite (Sin límites)</option>
                    <option value="gemini-3-flash-preview">gemini-3-flash-preview (Límite 18/día)</option>
                </select>
            </div>
        </div>

        <!-- Input para CÁMARA -->
        <input type="file" id="cameraInput" accept="image/*" capture="environment" style="display: none;" onchange="procesarImagen(this)">

        <!-- Input para GALERÍA -->
        <input type="file" id="galleryInput" accept="image/*" style="display: none;" onchange="procesarImagen(this)">

        <div class="d-grid gap-2">
            <button id="btnCaptureCamera" class="btn btn-success btn-big" onclick="abrirCamaraConValidacion()">
                📸 Capturar con Cámara
            </button>
            <button id="btnCaptureGallery" class="btn btn-info btn-big text-white" onclick="abrirGaleriaConValidacion()">
                📁 Seleccionar de Galería
            </button>
        </div>

        <!-- Loader -->
        <div id="loader" class="hidden">
            <div class="loader"></div>
            <p class="text-muted small">Procesando con IA...</p>
        </div>

        <!-- Resultado -->
        <div id="resultado" class="hidden"></div>
    </div>

    <!-- PANTALLA 3: FORMULARIO BAUTIZO -->
    <div id="screen-form" class="mobile-card hidden" style="max-width: 95%; width: 100%; max-height: 90vh; overflow-y: auto; padding: 15px; text-align: left;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="m-0 text-primary">Registrar Bautizo(s)</h5>
            <button class="btn btn-sm btn-outline-secondary" onclick="volverACamara()">Volver</button>
        </div>
        
        <!-- Texto extraído para referencia -->
        <div class="alert alert-info p-2 mb-3" style="font-size: 0.8rem;">
            <strong>Texto detectado:</strong>
            <div id="texto-extraido-display" style="max-height: 100px; overflow-y: auto; white-space: pre-wrap;"></div>
        </div>

        <!-- 🔹 CAMPOS GENERALES (LIBRO Y FOLIO) -->
        <div class="row mb-3">
            <div class="col-6">
                <label for="libroGeneral" class="form-label fw-bold">Libro:</label>
                <input type="number" class="form-control form-control-lg text-center" id="libroGeneral">
            </div>
            <div class="col-6">
                <label for="folioGeneral" class="form-label fw-bold">Folio:</label>
                <input type="number" class="form-control form-control-lg text-center" id="folioGeneral">
            </div>
        </div>

        <!-- 🔹 CONTENEDOR PARA FORMULARIOS DINÁMICOS -->
        <div id="forms-container">
            <!-- Los formularios se insertarán aquí vía JS -->
        </div>

        <div class="mt-3 mb-5">
            <button type="button" id="btnGuardarTodo" class="btn btn-success w-100 py-3 fw-bold hidden">GUARDAR TODO</button>
        </div>
    </div>

    <!-- 🔹 MODAL NUEVO MINISTRO (MOVIDO FUERA DE SCREEN-FORM) -->
    <div class="modal fade" id="modalNuevoMinistro" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalMinistroTitle">Verificar Ministro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning p-2 mb-3" style="font-size: 0.75rem;">
                        <i class="bi bi-exclamation-triangle"></i> Por favor, revise si el ministro ya está en el sistema antes de crear uno nuevo.
                    </div>

                    <div id="modalMinistroMsg">
                        <p class="small mb-1">En la Fe de Bautismo <b>N° <span id="lblActaPertenece"></span></b> se detectó a:</p>
                        <h5 class="text-center text-primary border-bottom pb-2" id="lblMinistroDetectado"></h5>
                    </div>

                    <!-- 🔹 SELECCIÓN DE EXISTENTE -->
                    <div id="divExistente" class="mb-4 mt-3 p-2 bg-light border rounded">
                        <label class="form-label small fw-bold">¿Ya está registrado? Selecciónelo:</label>
                        <select id="selMinistroExistente" class="form-select form-select-sm mb-2"></select>
                        <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="usarMinistroExistente()">Usar Seleccionado</button>
                    </div>

                    <hr>
                    <p class="small text-muted text-center">O regístrelo como nuevo aquí:</p>

                    <form id="formNuevoMinistro">
                        <div class="mb-2">
                            <label class="form-label small">Nombre</label>
                            <input type="text" id="newMinNom" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Apellido</label>
                            <input type="text" id="newMinApe" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Jerarquía</label>
                            <select id="newMinJer" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php 
                                if (isset($jerarquias)) {
                                    $jerarquias->data_seek(0);
                                    while ($j = $jerarquias->fetch_assoc()) {
                                        echo "<option value='{$j['CodJer']}'>" . htmlspecialchars($j['NomJer']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success w-100" onclick="guardarNuevoMinistro()">Registrar y Continuar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="view/js/jquery-3.6.0.min.js"></script>
    <script src="view/js/bootstrap.bundle.min.js"></script>
    <script src="view/js/sweetalert.js"></script>
    <script>
        let usuarioActual = "";
        let estaProcesando = false; // ✅ Flag de estado
        let estaVerificando = false; // 🔍 Nuevo estado: Introduciendo libro o en formulario
        let avisoInactividadMostrado = false; // 🛡️ Evitar repetición del mensaje
        let libroActual = null;
        let folioActual = null;
        let rutaImagenActual = ""; // 🔹 Variable para guardar la ruta de la foto actual
        let huboInteraccion = false; // ⚡ Flag para resetear inactividad
        let paginaCerrandose = false; // 🛡️ Evitar latidos al salir
        let heartbeatInterval = null; 
        let ultimaExtraccionIA = null; // 🔹 Almacena los datos originales de la IA para el cálculo de correcciones
        
        // 🔹 Datos de ministros en JS para validación
        let ministrosData = [
            <?php 
            if (isset($ministros)) { 
                $ministros->data_seek(0);
                while ($m = $ministros->fetch_assoc()) { 
                    $nombreCompleto = addslashes($m['Nom'] . " " . $m['Ape']);
                    echo "{id: '{$m['IdMinCel']}', nombre: '{$nombreCompleto}'},"; 
                } 
            } 
            ?>
        ];
        
        let colaMinistros = [];
        let actasPendientes = [];

        function toggleCaptureButtons(disabled) {
            $('#btnCaptureCamera, #btnCaptureGallery').prop('disabled', disabled);
        }

        function generarOpcionesMinistros() {
            let opts = '<option value="">Seleccione...</option>';
            ministrosData.forEach(m => {
                opts += `<option value="${m.id}">${m.nombre}</option>`;
            });
            return opts;
        }

        function actualizarSelectsEnVivo() {
            const options = generarOpcionesMinistros();
            $('select[name="IdMin"]').each(function() {
                const val = $(this).val();
                $(this).html(options);
                if(val) $(this).val(val);
            });
        }

        function volverACamara() {
            // 🗑️ Borrar la imagen del servidor si el cliente decide volver sin guardar
            if (rutaImagenActual) {
                $.post('?controller=sacrej&action=api_borrar_imagen_cancelada', { ruta: rutaImagenActual });
            }

            estaProcesando = false; // ✅ Liberar estado al volver
            estaVerificando = false; // 🔍 Regresar a estado normal
            toggleCaptureButtons(false); // ✅ Asegurar que botones estén activos
            $('#screen-form').addClass('hidden');
            $('#screen-camera').removeClass('hidden');
            $('#forms-container').empty(); // Limpiar formularios dinámicos
            $('#btnGuardarTodo').addClass('hidden');
            folioActual = null; // Resetear folio (cambia por página)
            $('#folioGeneral').val('');
            ultimaExtraccionIA = null;
            rutaImagenActual = "";
        }

        function crearHtmlFormulario(index) {
            const idSuffix = '_' + index;

            const ministrosOptions = generarOpcionesMinistros();

            return `
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Registro de Bautizo #${index + 1}</h6>
                </div>
                <div class="card-body">
                    <form id="formBautizo${idSuffix}" class="form-bautizo" data-index="${index}" onsubmit="return false;">
                        <!-- Estatus -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Estatus del Acta</label>
                            <select id="EstCel${idSuffix}" name="EstCel" class="form-select form-select-sm">
                                <option value="1" selected>Estandar</option>
                                <option value="2">Caso Especial</option>
                                <option value="0">Nulo</option>
                            </select>
                        </div>
                        <!-- Encabezado -->
                        <div class="row g-2">
                            <div class="col-12"><label class="form-label small">N°</label><input type="number" id="IdCel${idSuffix}" name="IdCel" class="form-control form-control-sm" required data-was-required="true"></div>
                            <input type="hidden" id="NumFol${idSuffix}" name="NumFol">
                            <input type="hidden" id="NumLib${idSuffix}" name="NumLib">
                            <input type="hidden" id="RutaImagen${idSuffix}" name="RutaImagen">
                        </div>
                        
                        <!-- Bautizado -->
                        <h6 class="mt-3 text-secondary border-bottom">Datos del Bautizado</h6>
                        <div class="row g-2">
                            <div class="col-6"><label class="form-label small">Nombre</label><input type="text" id="NomInd${idSuffix}" name="NomInd" class="form-control form-control-sm" required data-was-required="true"></div>
                            <div class="col-6"><label class="form-label small">Apellido</label><input type="text" id="ApeInd${idSuffix}" name="ApeInd" class="form-control form-control-sm" required data-was-required="true"></div>
                            <div class="col-6"><label class="form-label small">Sexo</label><select id="SexInd${idSuffix}" name="SexInd" class="form-select form-select-sm" required data-was-required="true"><option value="">Seleccione...</option><option value="1">Masculino</option><option value="2">Femenino</option></select></div>
                            <div class="col-6"><label class="form-label small">Filiación</label><select id="FilInd${idSuffix}" name="FilInd" class="form-select form-select-sm" required data-was-required="true"><option value="">Seleccione...</option><option value="1">Reconocido</option><option value="2">Legítimo</option><option value="3">Natural</option><option value="4">Ilegítimo</option><option value="0">No reconocido</option></select></div>
                        </div>
                        
                        <div class="row g-2 mt-1">
                            <div class="col-12"><label class="form-label small">Lugar de nacimiento</label><input type="text" id="LugNacInd${idSuffix}" name="LugNacInd" class="form-control form-control-sm" required data-was-required="true"></div>
                            <div class="col-12"><label class="form-label small">Fecha Nac.</label><input type="date" id="FecNacInd${idSuffix}" name="FecNacInd" class="form-control form-control-sm" required data-was-required="true"></div>
                        </div>

                        <!-- Padres -->
                        <h6 class="mt-3 text-secondary border-bottom">Padres</h6>
                        <div class="row g-2">
                            <div class="col-12"><strong>Madre:</strong></div>
                            <div class="col-6"><input type="text" id="NomMad${idSuffix}" name="NomMad" class="form-control form-control-sm" placeholder="Nombre" required data-was-required="true"></div>
                            <div class="col-6"><input type="text" id="ApeMad${idSuffix}" name="ApeMad" class="form-control form-control-sm" placeholder="Apellido" required data-was-required="true"></div>
                            <div class="col-12 mt-1"><strong>Padre:</strong></div>
                            <div class="col-6"><input type="text" id="NomPad${idSuffix}" name="NomPad" class="form-control form-control-sm" placeholder="Nombre"></div>
                            <div class="col-6"><input type="text" id="ApePad${idSuffix}" name="ApePad" class="form-control form-control-sm" placeholder="Apellido"></div>
                        </div>

                        <!-- Celebración -->
                        <h6 class="mt-3 text-secondary border-bottom">Detalles Celebración</h6>
                        <div class="row g-2">
                            <div class="col-6"><label class="form-label small">Fecha</label><input type="date" id="FechCel${idSuffix}" name="FechCel" class="form-control form-control-sm" required data-was-required="true"></div>
                            <div class="col-6"><label class="form-label small">Lugar de Bautizo</label><input type="text" id="Lugar${idSuffix}" name="Lugar" class="form-control form-control-sm" required data-was-required="true"></div>
                            <div class="col-12">
                                <label class="form-label small">Ministro</label>
                                <div class="input-group input-group-sm">
                                    <select id="IdMin${idSuffix}" name="IdMin" class="form-select" required data-was-required="true">${ministrosOptions}</select>
                                    <button class="btn btn-outline-secondary" type="button" onclick="abrirModalManual()">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Padrinos -->
                        <h6 class="mt-3 text-secondary border-bottom">Padrinos</h6>
                        <div class="row g-2">
                            <div class="col-12"><strong>Padrino:</strong></div>
                            <div class="col-6"><input type="text" id="Pad1Nom${idSuffix}" name="Pad1Nom" class="form-control form-control-sm" placeholder="Nombre" required data-was-required="true"></div>
                            <div class="col-6"><input type="text" id="Pad1Ape${idSuffix}" name="Pad1Ape" class="form-control form-control-sm" placeholder="Apellido" required data-was-required="true"></div>
                            <input type="hidden" id="Pad1Sex${idSuffix}" name="Pad1Sex" value="1">
                            
                            <div class="col-12 mt-1"><strong>Madrina:</strong></div>
                            <div class="col-6"><input type="text" id="Pad2Nom${idSuffix}" name="Pad2Nom" class="form-control form-control-sm" placeholder="Nombre" required data-was-required="true"></div>
                            <div class="col-6"><input type="text" id="Pad2Ape${idSuffix}" name="Pad2Ape" class="form-control form-control-sm" placeholder="Apellido" required data-was-required="true"></div>
                            <input type="hidden" id="Pad2Sex${idSuffix}" name="Pad2Sex" value="2">
                        </div>

                        <!-- Otros -->
                        <div class="mt-3">
                            <label class="form-label small">Observaciones</label><textarea id="NotMar${idSuffix}" name="NotMar" class="form-control form-control-sm"></textarea>
                            <label class="form-label small mt-2">Registro Civil</label><input type="text" id="RegCiv${idSuffix}" name="RegCiv" class="form-control form-control-sm">
                        </div>

                    </form>
                </div>
            </div>
            `;
        }

        function poblarFormulario(datos, index) {
            const idSuffix = '_' + index;

            // Helper para obtener valor, evitando 'null' como string
            const cleanValue = (value) => (value === null || value === 'null' || typeof value === 'undefined') ? '' : value;

            // Helper para fechas (Soporta: DD/MM/YYYY y "13 de Octubre de 2022")
            function formatearFechaParaInput(fechaStr) {
                if (!fechaStr || typeof fechaStr !== 'string') return '';
                
                // 1. Caso texto: "13 de Octubre de 2022"
                const meses = {
                    "enero": "01", "febrero": "02", "marzo": "03", "abril": "04", "mayo": "05", "junio": "06",
                    "julio": "07", "agosto": "08", "septiembre": "09", "octubre": "10", "noviembre": "11", "diciembre": "12"
                };
                
                // Regex para "13 de Octubre de 2022" (insensible a mayúsculas)
                const matchTexto = fechaStr.match(/(\d{1,2})\s+de\s+([a-zA-Z]+)\s+de\s+(\d{4})/i);
                if (matchTexto) {
                    const dia = matchTexto[1].padStart(2, '0');
                    const mesNombre = matchTexto[2].toLowerCase();
                    const anio = matchTexto[3];
                    if (meses[mesNombre]) {
                        return `${anio}-${meses[mesNombre]}-${dia}`;
                    }
                }

                // Intenta con varios separadores y formato DD/MM/YYYY
                const partes = fechaStr.match(/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})/);
                if (partes && partes.length === 4 && !matchTexto) {
                    const dia = partes[1].padStart(2, '0');
                    const mes = partes[2].padStart(2, '0');
                    const anio = partes[3];
                    return `${anio}-${mes}-${dia}`;
                }
                // Si ya viene como YYYY-MM-DD
                if (/^\d{4}-\d{2}-\d{2}$/.test(fechaStr)) {
                    return fechaStr;
                }
                return ''; // No se pudo parsear
            }

            // Helper para filiación
            function obtenerValorFiliacion(texto) {
                if (!texto) return '';
                const t = texto.toLowerCase();
                if (t.includes('reconocido')) return '1';
                if (t.includes('legítimo')) return '2';
                if (t.includes('natural')) return '3';
                if (t.includes('ilegítimo')) return '4';
                if (t.includes('no reconocido')) return '0';
                return '';
            }

            // Helper para sexo
            function obtenerValorSexo(texto) {
                if (!texto) return '';
                const t = texto.toLowerCase().trim();
                if (t.includes('femenin') || t === 'f' || t === '2' || t === 'mujer' || t === 'niña' || t === 'hembra') return '2';
                if (t.includes('masculin') || t === 'm' || t === '1' || t === 'niño' || t === 'varon' || t === 'varón' || t === 'hombre') return '1';
                return '';
            }

            // Poblar campos del formulario
            $('#IdCel' + idSuffix).val(cleanValue(datos['N°']));
            // El folio se maneja globalmente ahora
            $('#NomInd' + idSuffix).val(cleanValue(datos['Nombre del Bautizado']));
            $('#RutaImagen' + idSuffix).val(rutaImagenActual); // 🔹 Asignar ruta de imagen
            $('#ApeInd' + idSuffix).val(cleanValue(datos['Apellido del Bautizado']));
            let valSexo = cleanValue(datos['Sexo del Bautizado'] || datos['Sexo'] || datos['sexo']);
            $('#SexInd' + idSuffix).val(obtenerValorSexo(valSexo));
            $('#LugNacInd' + idSuffix).val(cleanValue(datos['Lugar de nacimiento']));
            $('#FecNacInd' + idSuffix).val(formatearFechaParaInput(cleanValue(datos['Fecha de nacimiento'])));
            $('#NomMad' + idSuffix).val(cleanValue(datos['Nombre de la Madre']));
            $('#ApeMad' + idSuffix).val(cleanValue(datos['Apellido de la Madre']));
            $('#NomPad' + idSuffix).val(cleanValue(datos['Nombre del Padre']));
           $('#ApePad' + idSuffix).val(cleanValue(datos['Apellido del Padre']));
            $('#FilInd' + idSuffix).val(obtenerValorFiliacion(cleanValue(datos['Filiacion'])));
            $('#FechCel' + idSuffix).val(formatearFechaParaInput(cleanValue(datos['Fecha de bautizo'])));
            // 🔹 Si la IA no devuelve lugar, poner el default. Si devuelve, usar el de la IA.
            const lugarBautizo = cleanValue(datos['Lugar de Bautizo']);
            $('#Lugar' + idSuffix).val(lugarBautizo || "Parroquia Sagrado Corazon de Jesus");
            
            // 🔹 Seleccionar Ministro por nombre
            let minNombre = cleanValue(datos['Ministro']);
            let minId = '';
            if (minNombre) {
                let minObj = ministrosData.find(m => m.nombre.toLowerCase() === minNombre.toLowerCase());
                if (minObj) minId = minObj.id;
            }
            $('#IdMin' + idSuffix).val(minId);

            $('#Pad1Nom' + idSuffix).val(cleanValue(datos['Nombre del Padrino']));
            $('#Pad1Ape' + idSuffix).val(cleanValue(datos['Apellido del Padrino']));
            $('#Pad2Nom' + idSuffix).val(cleanValue(datos['Nombre de la Madrina']));
            $('#Pad2Ape' + idSuffix).val(cleanValue(datos['Apellido de la madrina']));

            $('#NotMar' + idSuffix).val(cleanValue(datos['Observaciones']));
            $('#RegCiv' + idSuffix).val(cleanValue(datos['Registro Civil']));

            // 🔹 Disparar el change en el estatus para que la validación se aplique al poblar
            $('#EstCel' + idSuffix).trigger('change');
        }

        // 🔹 Restaurar sesión al cargar la página (si se actualizó)
        $(document).ready(function() {
            // ... (código de restauración de sesión sin cambios)
        });

        // 🔹 Restaurar sesión al cargar la página (si se actualizó)
        $(document).ready(function() {
            let guardado = sessionStorage.getItem('usuario_sacra');
            if(guardado) {
                $('#inputNombre').val(guardado);
                conectarUsuario(true); // true = es una reconexión automática
            }

            // ... (toda la lógica de guardado y generación de ID se moverá aquí)
        });

        function conectarUsuario(esReconexion = false) {
            const nombre = $('#inputNombre').val().trim();
            if (!nombre) {
                if(!esReconexion) Swal.fire('Error', 'Por favor ingresa tu nombre', 'warning');
                return;
            }

            // Registrar en el servidor
            $.post('?controller=sacrej&action=api_registrar_cliente', { nombre: nombre }, function(res) {
                if (res.success) {
                    usuarioActual = nombre;
                    sessionStorage.setItem('usuario_sacra', nombre); // ✅ Guardar sesión
                    $('#userDisplay').text(usuarioActual);
                    $('#screen-login').addClass('hidden');
                    $('#screen-wait').removeClass('hidden'); // Mostrar espera al conectar
                } else {
                    // Si falla la reconexión (ej. servidor cerrado), limpiamos
                    if(esReconexion) sessionStorage.removeItem('usuario_sacra');
                    else Swal.fire('Atención', res.message || 'No se pudo conectar.', 'warning');
                }
            }, 'json').fail(function() {
                if(!esReconexion) Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
            });
        }

        // 🔹 Desconectar manualmente (Botón Salir)
        function desconectar() {
            sessionStorage.removeItem('usuario_sacra'); // ✅ Limpiar sesión
            if (usuarioActual) {
                $.post('?controller=sacrej&action=api_desconectar_cliente', { nombre: usuarioActual }, function() {
                    usuarioActual = ""; // Limpiar para que no se dispare el beacon
                    location.reload();
                }).fail(function() {
                    // Si falla la conexión, forzamos la salida igual
                    usuarioActual = "";
                    location.reload();
                });
            } else {
                location.reload();
            }
        }

        // 🔹 Función centralizada para sacar al usuario
        function salirDelSistema(mensaje) {
            sessionStorage.removeItem('usuario_sacra'); // ✅ Limpiar sesión
            usuarioActual = "";
            $('#screen-camera').addClass('hidden');
            $('#screen-login').removeClass('hidden');
            $('#inputNombre').val('');
            $('#resultado').addClass('hidden').text('');
            $('#screen-form').addClass('hidden');
            
            Swal.fire({
                icon: 'warning',
                title: 'Desconectado',
                text: mensaje,
                timer: 3000,
                showConfirmButton: false
            });
        }

        // 🔹 Refactorización: Función para verificar estado antes de actuar
        function ejecutarSiServidorActivo(accion) {
            let activo = false;
            $.ajax({
                url: '?controller=sacrej&action=api_verificar_estado',
                async: false, // Síncrono para mantener el contexto del click
                dataType: 'json',
                success: function(res) {
                    if(res.estado === '1') activo = true;
                }
            });

            if (activo) {
                accion();
            } else {
                salirDelSistema('El servidor está desactivado. No puede realizar capturas.');
            }
        }

        // 🔹 Validación al pulsar el botón de cámara
        function abrirCamaraConValidacion() {
            ejecutarSiServidorActivo(() => {
                estaProcesando = true; // ✅ Iniciar estatus de procesamiento (para heartbeat)
                toggleCaptureButtons(true); // 🔒 Desactivar botones de captura
                document.getElementById('cameraInput').click();
            });
        }

        // 🔹 NUEVA: Validación al pulsar el botón de galería
        function abrirGaleriaConValidacion() {
            ejecutarSiServidorActivo(() => {
                estaProcesando = true; // ✅ Iniciar estatus de procesamiento (para heartbeat)
                toggleCaptureButtons(true); // 🔒 Desactivar botones de captura
                document.getElementById('galleryInput').click();
            });
        }

        function procesarImagen(input, filePrevia = null, numReintento = 0) { 
            const file = filePrevia || (input && input.files && input.files[0]);
            if (!file) return;

            // Mostrar loader
            $('#loader').removeClass('hidden');
            if (numReintento > 0) {
                // Si es un reintento, los botones ya están deshabilitados.
                // Solo necesitamos actualizar el texto del loader.
                $('#loader p').html(`Ejecutando reintento <b>#${numReintento}</b>...`);
            } else {
                $('#loader p').text('Procesando con IA...');
                const modelIA = $('#modelSelector').val();
                const modelText = modelIA.includes('lite') ? 'Lite' : 'Pro';
                $('#loader p').html(`Procesando con <b>Gemini ${modelText}</b>...`);
            }
                $('#resultado').addClass('hidden').text('');
                
                const formData = new FormData();
                const modelIA = $('#modelSelector').val();

                // 🚀 Evitar resubir la imagen si ya tenemos una ruta del intento anterior (ahorra datos y espacio)
                if (rutaImagenActual) {
                    formData.append('image_path', rutaImagenActual);
                } else {
                    formData.append('imagen', file);
                }

                formData.append('usuario_envio', usuarioActual); // Ajustado para coincidir con la validación del controlador
                formData.append('modelo_ia', modelIA);

                $.ajax({
                    url: '?controller=sacrej&action=api_procesar_imagen',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    complete: function(jqXHR) {
                        // 🛡️ Solo ocultar loader y liberar si NO es un error 503 (reintento)
                        // Si es 503, gestionarReintentoIA se encarga de mantener el estado
                        if (jqXHR.status !== 503) {
                            toggleCaptureButtons(false);
                            $('#loader').addClass('hidden');
                            estaProcesando = false;
                        }
                    },
                    success: function(res) {
                        
                        // 🔹 Adaptador para la nueva respuesta del controlador
                        let geminiRes = res;
                        
                        if (!res) {
                            Swal.fire('Error', 'La respuesta del servidor está vacía.', 'error');
                            return;
                        }

                        if (res.image_path) {
                            rutaImagenActual = res.image_path; // Guardamos la ruta
                            geminiRes = res.gemini_data;       // Usamos los datos de IA
                        }

                        // Gemini devuelve un JSON complejo, intentamos parsearlo
                        try {
                            // 1. Manejo de errores (del servidor local o de Gemini)
                            if (!geminiRes) {
                                throw new Error("No se recibieron datos de la IA.");
                            }

                            if (geminiRes.error) {
                                let msg = "";
                                if (typeof geminiRes.error === 'string') {
                                    msg = geminiRes.error;
                                } else if (geminiRes.error.message) {
                                    // Error de la API de Google (objeto)
                                    msg = "Google API Error: " + geminiRes.error.message;
                                } else {
                                    msg = "Error desconocido en la respuesta.";
                                }

                                // Si el error es por servidor desactivado, sacamos al usuario
                                if (msg.toLowerCase().includes('desactivado')) {
                                    salirDelSistema(msg);
                                    return;
                                }
                                toggleCaptureButtons(false);
                                Swal.fire('Atención', msg, 'warning');
                                return;
                            }

                            const procesarRespuestaIA = () => {
                                // 2. Validar estructura de respuesta exitosa de Gemini
                                let textoCompletoParaDisplay = "No se pudo extraer texto.";
                                
                                if (geminiRes.candidates && geminiRes.candidates.length > 0) {
                                const candidate = geminiRes.candidates[0];
                                if (candidate.content && candidate.content.parts && candidate.content.parts.length > 0) {
                                    let rawText = candidate.content.parts[0].text;
                                    
                                    // 1. Limpieza agresiva: Buscar el primer [ o { y el último ] o }
                                    let cleanText = rawText.replace(/```json/g, '').replace(/```/g, '').trim();
                                    
                                    // Encontrar el inicio del JSON
                                    const startMatch = cleanText.match(/[\[\{]/);
                                    if (startMatch) {
                                        cleanText = cleanText.substring(startMatch.index);
                                    }

                                    // 1.1 Limpieza extra: Eliminar comas finales que rompen JSON.parse
                                    cleanText = cleanText.replace(/,\s*([\]}])/g, '$1');
                                    
                                    textoCompletoParaDisplay = cleanText;
                                    
                                    // Intentar parsear el JSON que pedimos en el prompt
                                    try {
                                        let jsonResponse = null;
                                        
                                        // Intento 1: Parseo directo
                                        try {
                                            jsonResponse = JSON.parse(cleanText);
                                        } catch (e1) {
                                            // Intento 2: Si falla, puede que haya texto basura al final.
                                            // Buscamos el último ']' o '}' y cortamos ahí.
                                            const lastBracket = cleanText.lastIndexOf(']');
                                            const lastBrace = cleanText.lastIndexOf('}');
                                            const end = Math.max(lastBracket, lastBrace);
                                            
                                            if (end > 0) {
                                                const subText = cleanText.substring(0, end + 1);
                                                try {
                                                    jsonResponse = JSON.parse(subText);
                                                    textoCompletoParaDisplay = subText; // Actualizar display si tuvo éxito
                                                } catch (e2) {
                                                    throw e1; // Lanzar el error original si el recorte no funcionó
                                                }
                                            } else {
                                                throw e1;
                                            }
                                        }
                                        
                                        // 2. Normalizar a array (Manejar si devuelve objeto contenedor o array directo)
                                        let actas = [];
                                        if (Array.isArray(jsonResponse)) {
                                            actas = jsonResponse;
                                        } else if (typeof jsonResponse === 'object' && jsonResponse !== null) {
                                            // Caso A: Objeto que contiene un array (ej: { "actas": [...] })
                                            const keys = Object.keys(jsonResponse);
                                            const arrayKey = keys.find(k => Array.isArray(jsonResponse[k]));
                                            
                                            if (arrayKey) {
                                                actas = jsonResponse[arrayKey];
                                            } else {
                                                // Caso B: Objeto de objetos (ej: { "1": {...}, "2": {...} })
                                                // Verificamos si NO parece ser un acta individual (si no tiene campos clave como "Nombre")
                                                const esActaUnica = keys.some(k => k.toLowerCase().includes('nombre') || k.toLowerCase().includes('apellido'));
                                                
                                                if (!esActaUnica) {
                                                    // Asumimos que es un contenedor y extraemos sus valores
                                                    const valores = Object.values(jsonResponse);
                                                    // Filtramos solo los que sean objetos (las actas)
                                                    actas = valores.filter(v => typeof v === 'object' && v !== null);
                                                } else {
                                                    actas = [jsonResponse];
                                                }
                                            }
                                        }

                                        // 🔹 Extraer Folio (asumimos que es el mismo para todos en la foto)
                                        const primerFolio = actas.find(a => a['Folio N°'])?.['Folio N°'];
                                        if (primerFolio) {
                                            folioActual = primerFolio;
                                        } else {
                                            folioActual = '';
                                        }

                                        // 🧠 Guardar la extracción original para el cálculo de correcciones (IA Learning)
                                        ultimaExtraccionIA = JSON.parse(JSON.stringify(actas));

                                        // 🔹 Verificar ministros antes de mostrar formularios
                                        verificarMinistros(actas);

                                    } catch (e) {
                                        console.error("Error al parsear JSON de la IA o poblar el formulario:", e);
                                        $('#forms-container').html('<div class="alert alert-danger">Error al interpretar el JSON (' + e.message + ').<br>Revisa el texto detectado arriba e ingresa los datos manualmente.</div>');
                                        const formHtml = crearHtmlFormulario(0);
                                        $('#forms-container').append(formHtml);
                                        $('#btnGuardarTodo').removeClass('hidden');

                                        // 🔹 IMPORTANTE: Mostrar pantalla de formulario si hay error para que el usuario lo vea
                                        $('#screen-camera').addClass('hidden');
                                        $('#screen-form').removeClass('hidden');
                                    }
                                } else {
                                    // Caso: Bloqueo por seguridad u otra razón sin content
                                    if (candidate.finishReason) {
                                        textoCompletoParaDisplay = "La IA detuvo el procesamiento. Razón: " + candidate.finishReason;
                                    }
                                }
                            } else {
                                // 🔹 CASO: IA no devolvió nada útil -> Ofrecer manual
                                Swal.fire({
                                    title: 'Sin resultados',
                                    text: 'La IA no pudo leer el documento. ¿Deseas ingresar los datos manualmente?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Sí, manual',
                                    cancelButtonText: 'Cancelar'
                                }).then((r) => {
                                    if(r.isConfirmed) {
                                        ultimaExtraccionIA = null;
                                        // Simular un acta vacía para abrir el formulario
                                        mostrarFormularios([{}]);
                                    } else {
                                        estaProcesando = false; // ✅ Liberar si cancela
                                        toggleCaptureButtons(false);
                                    }
                                });
                                return; // Detener flujo automático
                            }

                            // 🔹 Mostrar formulario y el texto extraído como referencia
                            $('#texto-extraido-display').text(textoCompletoParaDisplay);
                            
                            // NOTA: Ya no mostramos la pantalla aquí, lo hace verificarMinistros()
                            };

                            if (libroActual) {
                                $('#libroGeneral').val(libroActual);
                                procesarRespuestaIA();
                            } else {
                                Swal.fire({
                                    title: 'Número de Libro',
                                    text: 'Por favor, ingresa el número del libro para estos registros.',
                                    input: 'number',
                                    inputAttributes: {
                                        autocapitalize: 'off'
                                    },
                                    showCancelButton: true,
                                    confirmButtonText: 'Confirmar',
                                    showLoaderOnConfirm: true,
                                    preConfirm: (numeroLibro) => {
                                        if (!numeroLibro || isNaN(parseInt(numeroLibro))) {
                                            Swal.showValidationMessage('Debes ingresar un número válido');
                                            return false;
                                        }
                                        return numeroLibro;
                                    },
                                    allowOutsideClick: () => !Swal.isLoading()
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        estaVerificando = true; // 🔍 Iniciando verificación al pedir libro
                                        libroActual = result.value;
                                        $('#libroGeneral').val(libroActual);
                                        procesarRespuestaIA();
                                    } else {
                                        estaVerificando = false;
                                    }
                                });
                            }

                        } catch (e) {
                            console.error(e);
                            Swal.fire('Error', 'Error al procesar la respuesta de la IA: ' + e.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 503) {
                            // 🧠 Recuperar la ruta de la imagen que el servidor ya guardó antes del error de IA
                            try {
                                const resErr = JSON.parse(xhr.responseText);
                                if (resErr.image_path) rutaImagenActual = resErr.image_path;
                            } catch(e) {}

                            gestionarReintentoIA(input, file, numReintento + 1);
                            return;
                        }
                        $('#loader').addClass('hidden');
                        estaProcesando = false;
                        Swal.fire('Error', 'Error de conexión al subir la imagen', 'error');
                    }
                });
                
                // Limpiar input para permitir tomar la misma foto de nuevo
                if (input) input.value = '';
        }

        function gestionarReintentoIA(input, file, siguienteReintento) {
            estaProcesando = true; // Mantener ocupado para el heartbeat
            $('#loader').removeClass('hidden');
            let segundos = 10;
            const timerInterval = setInterval(() => {
                $('#loader p').html(`IA saturada. Reintento <b>#${siguienteReintento}</b><br>Siguiente intento en: <b>${segundos}s</b>`);
                segundos--;
                if (segundos < 0) {
                    clearInterval(timerInterval);
                    procesarImagen(input, file, siguienteReintento);
                }
            }, 1000);
        }

        // 🔹 Lógica para verificar y registrar ministros faltantes
        function verificarMinistros(actas) {
            actasPendientes = actas;
            colaMinistros = [];
            let nombresVistos = new Set();

            actas.forEach(a => {
                let nombreIA = (a['Ministro'] || '').trim();
                // Limpiar espacios extra
                nombreIA = nombreIA.replace(/\s+/g, ' ');
                let actaNum = a['N°'] || 'N/A';
                
                if (nombreIA.length > 2) {
                    // Verificar si existe en la lista local (case insensitive)
                    let existe = ministrosData.some(m => m.nombre.toLowerCase() === nombreIA.toLowerCase());
                    
                    if (!existe && !nombresVistos.has(nombreIA.toLowerCase())) {
                        colaMinistros.push({ nombre: nombreIA, acta: actaNum });
                        nombresVistos.add(nombreIA.toLowerCase());
                    }
                }
            });

            if (colaMinistros.length > 0) {
                mostrarModalMinistro();
            } else {
                mostrarFormularios(actasPendientes);
            }
        }

        function mostrarModalMinistro() {
            if (colaMinistros.length === 0) {
                const el = document.getElementById('modalNuevoMinistro');
                const modal = bootstrap.Modal.getOrCreateInstance(el);
                modal.hide();
                mostrarFormularios(actasPendientes);
                return;
            }

            let item = colaMinistros[0];
            let nombreCompleto = item.nombre;
            
            $('#lblMinistroDetectado').text(nombreCompleto);
            $('#lblActaPertenece').text(item.acta);
            
            // Cargar combo de existentes y mostrar sección
            $('#divExistente').show();
            $('#selMinistroExistente').html(generarOpcionesMinistros());
            
            // Intentar separar nombre y apellido
            let partes = nombreCompleto.split(' ');
            let ape = partes.length > 1 ? partes.pop() : '';
            let nom = partes.join(' ');
            
            $('#newMinNom').val(nom);
            $('#newMinApe').val(ape);
            $('#newMinJer').val('');
            
            const el = document.getElementById('modalNuevoMinistro');
            const modal = bootstrap.Modal.getOrCreateInstance(el);
            modal.show();
        }

        function abrirModalManual() {
            $('#modalMinistroTitle').text('Nuevo Ministro');
            $('#modalMinistroMsg').html('<p>Ingrese los datos del nuevo ministro para registrarlo.</p>');
            
            $('#newMinNom').val('');
            $('#newMinApe').val('');
            $('#newMinJer').val('');
            $('#lblActaPertenece').text('Manual');
            $('#divExistente').hide(); // No tiene sentido sugerir si vienen de clicar el "+"
            
            const el = document.getElementById('modalNuevoMinistro');
            const modal = bootstrap.Modal.getOrCreateInstance(el);
            modal.show();
        }

        function usarMinistroExistente() {
            let idMin = $('#selMinistroExistente').val();
            let nombreMin = $('#selMinistroExistente option:selected').text();

            if (!idMin) {
                Swal.fire('Atención', 'Seleccione un ministro de la lista o regístrelo abajo.', 'warning');
                return;
            }

            const itemIA = colaMinistros[0];
            if (itemIA) {
                actasPendientes.forEach(a => {
                    let aMinistro = (a['Ministro'] || '').trim().replace(/\s+/g, ' ');
                    if (aMinistro.toLowerCase() === itemIA.nombre.toLowerCase()) {
                        a['Ministro'] = nombreMin;
                    }
                });
            }

            colaMinistros.shift();
            mostrarModalMinistro();
        }

        function guardarNuevoMinistro() {
            let nom = $('#newMinNom').val().trim();
            let ape = $('#newMinApe').val().trim();
            let jer = $('#newMinJer').val();

            if (!nom || !ape || !jer) {
                Swal.fire('Atención', 'Complete todos los campos del ministro', 'warning');
                return;
            }

            $.post('?controller=sacrej&action=agregar_ministro', { Nom: nom, Ape: ape, CodJer: jer }, function(res) {
                if (res.status === 'ok') {
                    const nuevoNombre = nom + ' ' + ape;

                    // Agregar a la lista local
                    ministrosData.push({
                        id: res.id,
                        nombre: nuevoNombre
                    });
                    
                    if (colaMinistros.length > 0) {
                        // 🔹 ACTUALIZAR ACTAS PENDIENTES (Flujo IA)
                        // Actualizamos el nombre en las actas pendientes para que coincida con el registrado
                        const itemIA = colaMinistros[0]; 
                        actasPendientes.forEach(a => {
                            let aMinistro = (a['Ministro'] || '').trim().replace(/\s+/g, ' ');
                            if (aMinistro.toLowerCase() === itemIA.nombre.toLowerCase()) {
                                a['Ministro'] = nuevoNombre;
                            }
                        });
                        
                        // Siguiente en la cola
                        colaMinistros.shift();
                        mostrarModalMinistro();
                    } else {
                        // 🔹 Flujo Manual
                        actualizarSelectsEnVivo();
                        const el = document.getElementById('modalNuevoMinistro');
                        const modal = bootstrap.Modal.getOrCreateInstance(el);
                        modal.hide();
                    }
                    
                    // Feedback discreto
                    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                    Toast.fire({ icon: 'success', title: 'Ministro registrado' });
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }, 'json');
        }

        function mostrarFormularios(actas) {
            $('#forms-container').empty();
            estaVerificando = true; // 🔍 Activamos estado de verificación

            // Mostrar valores globales
            $('#folioGeneral').val(folioActual);

            actas.forEach((datosActa, index) => {
                if (datosActa && typeof datosActa === 'object') {
                    const formHtml = crearHtmlFormulario(index);
                    $('#forms-container').append(formHtml);
                    poblarFormulario(datosActa, index);
                    $('#NumLib_' + index).val(libroActual);
                    $('#NumFol_' + index).val(folioActual);
                    $('#RutaImagen_' + index).val(rutaImagenActual);
                }
            });
            
            if (actas.length > 0) $('#btnGuardarTodo').removeClass('hidden');
            
            $('#screen-camera').addClass('hidden');
            $('#screen-form').removeClass('hidden');
        }

        function verificarEstadoLatido() {
            if (!usuarioActual || paginaCerrandose) return;

            const modelIA = $('#modelSelector').val();
            $.post('?controller=sacrej&action=api_heartbeat', { 
                nombre: usuarioActual,
                processing: String(estaProcesando),
                verifying: String(estaVerificando),
                active: String(huboInteraccion), // ⚡ Informar actividad al servidor
                modelo_ia: modelIA
            }, function(res) {
                huboInteraccion = false; // Resetear flag

                if (res.estado !== '1') {
                    salirDelSistema('El servidor ha sido desactivado.');
                } else if (res.conectado === false) {
                    salirDelSistema('Has sido desconectado.');
                } else {
                    // 🔔 Mensaje de advertencia a los 4 minutos (240 segundos)
                    if (res.inactividad >= 240 && !avisoInactividadMostrado) {
                        avisoInactividadMostrado = true;
                        Swal.fire({
                            icon: 'warning',
                            title: '¡Atención!',
                            text: 'Tu sesión se cerrará en un minuto por inactividad.',
                            timer: 4000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                    }
                    
                    // Resetear el flag si el usuario vuelve a tener actividad
                    if (res.inactividad < 240) {
                        avisoInactividadMostrado = false;
                    }

                    // Mostrar el correo de la API Key en uso
                    if (res.api_email) {
                        $('#apiDisplay').text('IA: ' + res.api_email);
                    }

                    // 📊 Actualizar contador de peticiones IA
                    if (res.ia_quota) {
                        const q = res.ia_quota;
                        $('#ia-quota-badge')
                            .text(`IA: ${q.restante} disponibles`)
                            .removeClass('hidden bg-success bg-warning bg-danger')
                            .addClass(q.restante > 10 ? 'bg-success' : (q.restante > 3 ? 'bg-warning' : 'bg-danger'));
                    } else {
                        $('#ia-quota-badge').addClass('hidden');
                    }

                    if (res.status === 'allowed') {
                        $('#screen-wait').addClass('hidden');
                        if ($('#screen-form').hasClass('hidden')) {
                            $('#screen-camera').removeClass('hidden');
                        }
                    } else {
                        $('#screen-camera').addClass('hidden');
                        $('#screen-wait').removeClass('hidden');
                    }
                }
            }, 'json').fail(function() {
                console.warn("Fallo temporal de conexión...");
            });
        }

        // 🔄 Polling cada 3 segundos (un poco más lento para ser más estable)
        heartbeatInterval = setInterval(verificarEstadoLatido, 3000);

        // 🔹 Lógica del Formulario (Generación de ID y Envío)
        $(document).ready(function() {
            // ⚡ Detectar actividad para resetear inactividad en el servidor
            $(document).on('click input keydown change', function() {
                huboInteraccion = true;
            });

            // ✅ Detectar si el usuario cancela la selección en el selector de archivos (Cámara/Galería)
            // Esto captura el momento en que se cierra el diálogo sin elegir nada.
            document.getElementById('cameraInput').addEventListener('cancel', () => {
                estaProcesando = false;
                toggleCaptureButtons(false); // Re-activar botones si se cancela
            });
            document.getElementById('galleryInput').addEventListener('cancel', () => {
                estaProcesando = false;
                toggleCaptureButtons(false); // Re-activar botones si se cancela
            });

            let guardado = sessionStorage.getItem('usuario_sacra');
            if(guardado) {
                $('#inputNombre').val(guardado);
                conectarUsuario(true);
            }

            // --- Lógica de validación por Estatus para formularios dinámicos ---
            $('#forms-container').on('change', 'select[name="EstCel"]', function() {
                const form = $(this).closest('form');
                const estatus = $(this).val();
                const isStandard = (estatus == '1');

                form.find('[data-was-required="true"]').each(function() {
                    $(this).prop('required', isStandard);
                });
            });

            // Actualizar libroActual cuando se edita el campo
            $('#libroGeneral').on('input change', function() {
                libroActual = $(this).val();
                $('input[name="NumLib"]').val(libroActual);
            });

            // Actualizar folioActual cuando se edita el campo
            $('#folioGeneral').on('input change', function() {
                folioActual = $(this).val();
                $('input[name="NumFol"]').val(folioActual);
            });

            // Enviar formulario
            $('#btnGuardarTodo').click(async function() {
                const $btn = $(this);
                if ($btn.data("sending")) return;
                
                const forms = $('.form-bautizo');
                if (forms.length === 0) return;

                // 🔹 Validar todos los formularios usando HTML5 checkValidity
                let allFormsValid = true;
                forms.each(function() {
                    if (!this.checkValidity()) {
                        // Crear un botón de submit temporal para mostrar los mensajes de error del navegador
                        let tempSubmit = document.createElement('button');
                        $(this).append(tempSubmit);
                        tempSubmit.click();
                        $(this).remove(tempSubmit);
                        allFormsValid = false;
                        return false; // Detener el bucle
                    }
                });

                if (!allFormsValid) {
                    Swal.fire('Falta información', 'Por favor, complete todos los campos obligatorios.', 'warning');
                    return;
                }

                // --- 🧠 CÁLCULO DE CORRECCIONES PARA APRENDIZAJE IA (HILO OCULTO) ---
                if (ultimaExtraccionIA) {
                    let correcciones = [];
                    forms.each(function() {
                        const idx = $(this).data('index');
                        const original = (Array.isArray(ultimaExtraccionIA)) ? (ultimaExtraccionIA[idx] || {}) : (ultimaExtraccionIA || {});
                        const $f = $(this);
                        let corActa = { "N°": $f.find('[name="IdCel"]').val() };
                        let mod = false;
                        
                        // Normalizador de fechas para comparar DD/MM/YYYY
                        const dmy = (d) => { if(!d) return ''; const p = d.split('-'); return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : d; };
                        const sexMap = { "1": "Masculino", "2": "Femenino" };

                        const check = (fName, iaK, isD = false, isS = false) => {
                            let valF = $f.find(`[name="${fName}"]`).val();
                            if (isD) valF = dmy(valF);
                            if (isS) valF = sexMap[valF] || valF;
                            let valI = original[iaK];
                            let cleanI = (valI === null || valI === 'null' || typeof valI === 'undefined') ? '' : String(valI).trim();
                            if (String(valF).trim().toLowerCase() !== cleanI.toLowerCase() && cleanI !== '') {
                                corActa[iaK] = valF;
                                mod = true;
                            }
                        };

                        check('NomInd', 'Nombre del Bautizado');
                        check('ApeInd', 'Apellido del Bautizado');
                        check('SexInd', 'Sexo del Bautizado', false, true);
                        check('NomPad', 'Nombre del Padre');
                        check('ApePad', 'Apellido del Padre');
                        check('NomMad', 'Nombre de la Madre');
                        check('ApeMad', 'Apellido de la Madre');
                        check('LugNacInd', 'Lugar de nacimiento');
                        check('FecNacInd', 'Fecha de nacimiento', true);
                        check('FechCel', 'Fecha de bautizo', true);
                        check('NotMar', 'Observaciones');

                        if (mod) correcciones.push(corActa);
                    });

                    if (correcciones.length > 0) {
                        $.post('?controller=sacrej&action=api_registrar_correccion', {
                            usuario_envio: usuarioActual,
                            json_fix: JSON.stringify(correcciones),
                            ia_raw: JSON.stringify(ultimaExtraccionIA)
                        });
                    }
                }

                $btn.data("sending", true).prop("disabled", true).text("Guardando...");
                Swal.fire({ title: "Guardando...", didOpen: () => Swal.showLoading() });

                let guardados = 0;
                let errores = 0;

                for (let i = 0; i < forms.length; i++) {
                    const form = $(forms[i]);
                    if (form.data('saved')) continue; // Ya guardado

                    const formData = form.serializeArray();
                    const jsonData = {};
                    formData.forEach((item) => (jsonData[item.name] = item.value));
                    jsonData['usuario_envio'] = usuarioActual; // Añadir quién envía

                    try {
                        console.log('Sending data for form', i, ':', jsonData);
                        const res = await $.ajax({
                            url: "?controller=sacrej&action=api_enviar_bautizos_temporal",
                            type: "POST",
                            dataType: "json",
                            data: jsonData
                        });
                        console.log('Response:', res);

                        if (res.status === "ok") {
                            guardados++;
                            form.data('saved', true);
                            form.find('input, select, textarea').prop('disabled', true);
                            form.find('.card-header').addClass('bg-info text-white').append(' - ENVIADO');
                        } else {
                            errores++;
                        }
                    } catch (e) {
                        errores++;
                    }
                }

                Swal.close();
                $btn.data("sending", false).prop("disabled", false).text("GUARDAR TODO");

                if (errores === 0) {
                    Swal.fire('Éxito', `Se enviaron ${guardados} registros al servidor.`, 'success')
                        .then(() => volverACamara());
                } else {
                    Swal.fire('Atención', `Se enviaron ${guardados} registros. Hubo ${errores} errores. Revise los datos e intente de nuevo.`, 'warning');
                }
            });
        });
    </script>
</body>
</html>
