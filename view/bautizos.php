<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<div class="container mt-4">
  <h4 class="text-center mb-4">Registro de Bautizos</h4>

  <form id="formBautizo" onsubmit="return false;">

    <!-- 📘 Estatus del Acta -->
    <div class="mb-3">
      <label class="form-label">Estatus del Acta</label>
      <select id="EstCel" name="EstCel" class="form-select" required>
        <option value="1" selected>Estandar</option>
        <option value="2">Caso Especial</option>
        <option value="0">Nulo</option>
      </select>
    </div>

    <!-- 🟦 Encabezado -->
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Código de Celebración</label>
        <input type="number" id="IdCel" name="IdCel" class="form-control" min="1" required data-was-required="true">
      </div>
      <div class="col-md-4">
        <label class="form-label">Libro N°</label>
        <input type="number" id="NumLib" name="NumLib" class="form-control" required data-was-required="true">
      </div>
      <div class="col-md-4">
        <label class="form-label">Folio N°</label>
        <input type="number" id="NumFol" name="NumFol" class="form-control" required data-was-required="true">
      </div>
    </div>

    <!-- 📘 Tipo de Celebración -->
    <div class="row g-3 mt-3">
      <div class="col-md-6">
        <label class="form-label">Tipo de Celebración</label>
        <select id="TipCel" name="TipCel" class="form-select" required data-was-required="true">
          <option value="">Seleccione...</option>
          <?php
          if (isset($this) && property_exists($this, 'model')) {
            $tipos = $this->model->obtener_todos("tipo_celebracion");
            while ($t = $tipos->fetch_assoc()) {
              echo "<option value='{$t['IdTip']}'>" . htmlspecialchars($t['DesTip']) . "</option>";
            }
          }
          ?>
        </select>
      </div>
    </div>

    <!-- 🧒 Datos del bautizado -->
    <div class="row g-3 mt-3">
      <div class="col-md-4">
        <label class="form-label">Nombre(s) del Bautizado</label>
        <input type="text" id="NomInd" name="NomInd" class="form-control" required data-was-required="true">
      </div>
      <div class="col-md-4">
        <label class="form-label">Apellido(s)</label>
        <input type="text" id="ApeInd" name="ApeInd" class="form-control" required data-was-required="true">
      </div>
      <div class="col-md-4">
        <label class="form-label">Sexo</label>
        <select id="SexInd" name="SexInd" class="form-select" required data-was-required="true">
          <option value="">Seleccione…</option>
          <option value="1">Masculino</option>
          <option value="2">Femenino</option>
        </select>
      </div>
    </div>

    <!-- Campo oculto ID individuo -->
    <input type="hidden" id="IdInd" name="IdInd">

    <!-- 👩 Madre -->
    <div class="mt-4">
      <strong>Datos de la Madre (obligatorios)</strong>
      <div class="row g-3 mt-1">
        <div class="col-md-6">
          <label class="form-label">Nombre</label>
          <input type="text" id="NomMad" name="NomMad" class="form-control" required data-was-required="true">
        </div>
        <div class="col-md-6">
          <label class="form-label">Apellido</label>
          <input type="text" id="ApeMad" name="ApeMad" class="form-control" required data-was-required="true">
        </div>
      </div>
    </div>

    <!-- 👨 Padre -->
    <div class="mt-3">
      <strong>Datos del Padre (opcional)</strong>
      <div class="row g-3 mt-1">
        <div class="col-md-6">
          <label class="form-label">Nombre</label>
          <input type="text" id="NomPad" name="NomPad" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Apellido</label>
          <input type="text" id="ApePad" name="ApePad" class="form-control">
        </div>
      </div>
    </div>

    <!-- 👶 Filiación y nacimiento -->
    <div class="row g-3 mt-3">
      <div class="col-md-3">
        <label class="form-label">Filiación</label>
        <select id="FilInd" name="FilInd" class="form-select" required data-was-required="true">
          <option value="">Seleccione…</option>
          <option value="1">Reconocido</option>
          <option value="2">Legítimo</option>
          <option value="3">Natural</option>
        </select>
      </div>
      <div class="col-md-5">
        <label class="form-label">Lugar de nacimiento</label>
        <input type="text" id="LugNacInd" name="LugNacInd" class="form-control" required data-was-required="true">
      </div>
      <div class="col-md-4">
        <label class="form-label">Fecha de nacimiento</label>
        <input type="date" id="FecNacInd" name="FecNacInd" class="form-control" required data-was-required="true">
      </div>
    </div>

    <!-- ✝️ Celebración -->
    <div class="row g-3 mt-3">
      <div class="col-md-3">
        <label class="form-label">Fecha del Bautizo</label>
        <input type="date" id="FechCel" name="FechCel" class="form-control" required data-was-required="true">
      </div>
      <div class="col-md-4">
        <label class="form-label">Lugar de la Celebración</label>
        <input type="text" id="Lugar" name="Lugar" class="form-control" required data-was-required="true">
      </div>
      <div class="col-md-5">
        <label class="form-label">Ministro Celebrante</label>
        <select id="IdMin" name="IdMin" class="form-select" required data-was-required="true">
          <option value="">Seleccione…</option>
          <?php
          if (isset($this) && property_exists($this, 'model')) {
            $mins = $this->model->obtener_todos("ministro_celebrante");
            while ($m = $mins->fetch_assoc()) {
              echo "<option value='{$m['IdMinCel']}'>" . htmlspecialchars($m['Nom'] . " " . $m['Ape']) . "</option>";
            }
          }
          ?>
        </select>
      </div>
    </div>

    <!-- 👩‍❤️‍👨 Padrinos -->
    <div class="row g-3 mt-3">
      <div class="col-12"><strong>Datos del Padrino</strong></div>

      <div class="col-md-4">
        <label class="form-label">Nombre </label>
        <input type="text" id="Pad1Nom" name="Pad1Nom" class="form-control" required data-was-required="true">
      </div>
      <div class="col-md-4">
        <label class="form-label">Apellido</label>
        <input type="text" id="Pad1Ape" name="Pad1Ape" class="form-control" required data-was-required="true">
      </div>
      <!-- Sexo enviado oculto: 1 = Masculino (padrino) -->
      <input type="hidden" id="Pad1Sex" name="Pad1Sex" value="1">

      <div class="col-12"><strong>Datos de la Madrina</strong></div>

      <div class="col-md-4">
        <label class="form-label">Nombre</label>
        <input type="text" id="Pad2Nom" name="Pad2Nom" class="form-control" required data-was-required="true">
      </div>
      <div class="col-md-4">
        <label class="form-label">Apellido</label>
        <input type="text" id="Pad2Ape" name="Pad2Ape" class="form-control" required data-was-required="true">
      </div>
      <!-- Sexo enviado oculto: 2 = Femenino (madrina) -->
      <input type="hidden" id="Pad2Sex" name="Pad2Sex" value="2">
    </div>

    <!-- 📝 Observaciones -->
    <div class="row g-3 mt-3">
      <div class="col-md-6">
        <label class="form-label">Observaciones / Notas marginales</label>
        <textarea id="NotMar" name="NotMar" class="form-control"></textarea>
      </div>
      <div class="col-md-4">
        <label class="form-label">Registro Civil Nº</label>
        <input type="number" id="RegCiv" name="RegCiv" class="form-control">
      </div>
    </div>

    <!-- 📸 Sección de Carga de Imagen Manual -->
    <div class="card mt-5 border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Cargar Imagen del Folio (Manual)</h5>
        </div>
        <div class="card-body">
            <p class="card-text small text-muted">Utilice esta sección para adjuntar una imagen del folio si no está usando el sistema de digitalización móvil con IA.</p>
            
            <div class="mb-3">
                <label for="digitalizadorNombreManual" class="form-label">Nombre del Digitalizador:</label>
                <input type="text" id="digitalizadorNombreManual" class="form-control" placeholder="Ej: Juan Pérez" required>
            </div>

            <button type="button" class="btn btn-info w-100 mb-3" id="btnActivarRecepcion">
                Mostrar QR para Subir Imagen
            </button>

            <div id="manualUploadArea" class="text-center hidden">
                <p class="text-muted small">Escanee este código QR con un móvil para subir la imagen:</p>
                <div id="qrcode" class="d-inline-block p-2 border rounded mb-3"></div>
                <p class="text-muted small">O acceda a: <a href="#" id="manualUploadLink" target="_blank"></a></p>
                <p class="text-info small mb-3" id="manualUploadHelp"></p>
                <div class="spinner-border text-primary hidden" role="status" id="manualUploadSpinner">
                    <span class="visually-hidden">Esperando imagen...</span>
                </div>
                <p class="text-primary mt-2 hidden" id="manualUploadStatus">Esperando imagen...</p>
            </div>

            <div id="imagenManualPreview" class="mt-3 hidden">
                <h6>Imagen Recibida:</h6>
                <img id="previewImg" src="" alt="Imagen del Folio" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                <p class="small text-muted mt-2">Digitalizado por: <span id="previewDigitalizador"></span></p>
                <a id="btnVerImagenManual" href="#" target="_blank" class="btn btn-sm btn-outline-success w-100 mb-2 hidden">👁️ Ver Imagen Completa</a>
                <button type="button" class="btn btn-sm btn-outline-danger w-100" id="btnQuitarImagenManual">Quitar Imagen</button>
            </div>

            <input type="hidden" id="RutaImagenManual" name="RutaImagen">
            <input type="hidden" id="NombreDigitalizadorManual" name="NombreDigitalizador">
        </div>
    </div>

    <!-- Botones (Movidos al final) -->
    <div class="text-end mt-4">
      <button type="reset" class="btn btn-secondary">Limpiar</button>
      <button type="button" id="btnGuardarBautizo" class="btn btn-success">Guardar</button>
    </div>
  </form>
</div>

<!-- Incluir qrcode.min.js al final del body para asegurar que el DOM esté listo -->
<script src="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAM

E']), '/\\'); ?>/view/js/qrcode.min.js"></script>
<script>
  if (typeof QRCode === 'undefined') {
    console.warn("WARNING: Local QRCode library is NOT loaded after script tag. Intentando fallback CDN...");
    var fallbackScript = document.createElement('script');
    fallbackScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
    fallbackScript.onload = function() {
      console.log("DEBUG: QRCode library loaded from CDN fallback.");
    };
    fallbackScript.onerror = function() {
      console.error("ERROR: QRCode library failed to load from local and CDN fallback. Verifique que view/js/qrcode.min.js sea accesible.");
    };
    document.head.appendChild(fallbackScript);
  } else {
    console.log("DEBUG: QRCode library IS loaded after script tag.");
  }
</script>

<script>
  (function () {
    console.log("📄 bautizos.php cargado correctamente");

    // 🕓 Esperar a que jQuery esté disponible
    function whenReady(fn) {
      if (window.jQuery) fn();
      else setTimeout(() => whenReady(fn), 200);
    }

    whenReady(() => {
      console.log("✅ jQuery detectado, activando scripts...");

      // Asegurarse de que la sección de previsualización de imagen y el botón "Ver Imagen" estén ocultos al cargar la página
      $('#imagenManualPreview').hide();
      $('#btnVerImagenManual').hide();

      // --- Lógica para validación por Estatus ---
      const form = $('#formBautizo');
      const estatusSelect = $('#EstCel');

      estatusSelect.on('change', function() {
          const estatus = $(this).val();
          if (estatus == '1') {
              // Estándar (1): Todo lo marcado originalmente es obligatorio
              form.find('[data-was-required="true"]').prop('required', true);
          } else {
              // Caso Especial (2) o Nulo (0): Solo Ministro, Nombre y Sexo son obligatorios
              // 1. Quitamos el requerimiento a todos los campos marcados
              form.find('[data-was-required="true"]').prop('required', false);
              // 2. Forzamos la obligatoriedad solo en los 3 campos solicitados
              $('#IdMin, #NomInd, #SexInd').prop('required', true);
          }
      }).trigger('change'); // Disparar al cargar para establecer el estado inicial

      // --- Fin de la lógica de Estatus ---

      const endpoint = "index.php?controller=sacrej&action=agregar_bautizo";

      // 🔔 Notificación genérica
      function notify(type, title, text) {
        if (window.Swal && Swal.fire) Swal.fire({ icon: type, title, text });
        else alert(title + "\n" + text);
      }

      /* ======================
         1. FORMATEO / VALIDACIONES
         ====================== */

      // Para nombres (solo letras y espacios, luego capitaliza cada palabra)
      function formatearNombre(valor) {
        if (!valor) return "";

        // Solo letras (incluyendo tildes) y espacios
        valor = valor.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, "");

        // Reducir espacios múltiples a uno
        valor = valor.replace(/\s+/g, " ").trim().toLowerCase();
        if (!valor) return "";

        // Primera letra de CADA palabra en mayúscula
        return valor
          .split(" ")
          .map(p => p.charAt(0).toUpperCase() + p.slice(1))
          .join(" ");
      }

      // Para campos que deben permitir números y caracteres especiales,
      // pero capitalizar la primera letra de cada palabra.
      function formatearTextoEspecial(valor) {
        if (!valor) return "";
        // Normalizar espacios múltiples y recortar
        valor = valor.replace(/\s+/g, " ").trim();
        if (!valor) return "";

        return valor
          .split(" ")
          .map(w => {
            // Si la palabra está vacía, retornarla tal cual
            if (w.length === 0) return w;
            const first = w.charAt(0).toUpperCase();
            const rest = w.slice(1);
            return first + rest;
          })
          .join(" ");
      }

      const camposNombres = [
        "#NomInd", "#ApeInd",
        "#NomMad", "#ApeMad",
        "#NomPad", "#ApePad",
        "#Pad1Nom", "#Pad1Ape",
        "#Pad2Nom", "#Pad2Ape"
      ];

      // Nombres: limpiar caracteres no permitidos mientras se escribe,
      // y al blur aplicar formatearNombre()
      camposNombres.forEach(sel => {
        $(sel).on("input", function () {
          let v = $(this).val();
          v = v.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, "");
          $(this).val(v);
        });

        $(sel).on("blur", function () {
          const nuevo = formatearNombre($(this).val());
          $(this).val(nuevo);
        });
      });

      // Campos que deben permitir números y caracteres especiales:
      // LugNacInd, Lugar
      ["#LugNacInd", "#Lugar"].forEach(sel => {
        // No filtramos al escribir (permitir todo), sólo normalizamos espacios
        $(sel).on("input", function () {
          // opcional: reducir múltiples espacios en tiempo real
          let v = $(this).val();
          // no eliminar caracteres especiales ni números
          // pero sí evitar múltiples espacios al principio/medio
          v = v.replace(/\s{2,}/g, " ");
          $(this).val(v);
        });

        // Al salir: aplicar capitalización de la primera letra de cada palabra
        $(sel).on("blur", function () {
          const nuevo = formatearTextoEspecial($(this).val());
          $(this).val(nuevo);
        });
      });

      /* ======================
         2. VALIDAR NUMÉRICOS
         ====================== */

      const camposNumericos = ["#IdCel", "#NumLib", "#NumFol"];

      camposNumericos.forEach(sel => {
        // Solo dígitos
        $(sel).on("input", function () {
          this.value = this.value.replace(/\D/g, "");
        });

        // Bloquear e, +, -, .
        $(sel).on("keydown", function (e) {
          const prohibidos = ["e", "E", "+", "-", "."];
          if (prohibidos.includes(e.key)) {
            e.preventDefault();
          }
        });
      });

      /* =========================================
         3. GENERAR ID DEL INDIVIDUO AUTOMÁTICO
         ========================================= */

      function generarIdInd() {
        const f = $("#FecNacInd").val();
        const n = $("#NomInd").val().trim();
        const a = $("#ApeInd").val().trim();
        if (!f || !n || !a) return "";
        return f.replaceAll("-", "") + n[0].toUpperCase() + a[0].toUpperCase();
      }

      $("#NomInd, #ApeInd, #FecNacInd").on("change blur", function () {
        const id = generarIdInd();
        if (id) $("#IdInd").val(id);
      });

      // 🚫 Evitar envío clásico del formulario
      $("#formBautizo").on("submit", function (e) {
        e.preventDefault();
      });

      /* ==================================
         4. VALIDACIÓN FECHAS (NAC <> BAUT)
         ================================== */

      function fechasValidas() {
        const fnac  = $("#FecNacInd").val();
        const fbaut = $("#FechCel").val();

        if (!fnac || !fbaut) return true; // el required del HTML se encarga

        const dnac  = new Date(fnac);
        const dbaut = new Date(fbaut);

        if (dbaut < dnac) {
          notify("warning", "Fechas inválidas", "La fecha del bautizo no puede ser menor a la fecha de nacimiento.");
          return false;
        }
        return true;
      }

      /* =============================
         5. BOTÓN GUARDAR BAUTIZO
         ============================= */

      $("#btnGuardarBautizo").off("click").on("click", function () {
        const $btn = $(this);
        const form = document.getElementById('formBautizo');

        // 🧩 Antirrebote: evitar doble clic
        if ($btn.data("sending")) return;
        $btn.data("sending", true);

        // 🔹 Validación de campos obligatorios con mensaje específico
        if (!form.checkValidity()) {
            let missingFields = [];
            $(form).find(':invalid').each(function() {
                let labelText = $(this).closest('div').find('label').first().text() || $(this).attr('placeholder') || $(this).attr('name');
                if (labelText) {
                    missingFields.push(labelText.trim().replace('*', '').replace(':', ''));
                }
            });
            form.reportValidity();
            const msg = "Faltan los siguientes campos obligatorios: " + [...new Set(missingFields)].join(", ");
            notify("warning", "Campos incompletos", msg);
            $btn.data("sending", false);
            return;
        }

        // 📸 Validar que la foto sea obligatoria en cualquier estatus
        const rutaImagen = $('#RutaImagenManual').val();
        if (!rutaImagen) {
            notify("warning", "Imagen Faltante", "La carga de la foto del folio es obligatoria para poder guardar el registro.");
            $btn.data("sending", false);
            return;
        }

        // Validar fechas
        if (!fechasValidas()) {
          $btn.data("sending", false);
          return;
        }

        // 🔄 Recolectar datos del formulario
        const formData = $("#formBautizo").serializeArray();
        const jsonData = {};
        formData.forEach((item) => (jsonData[item.name] = item.value));

        // 🆕 Añadir datos de imagen manual si existen
        jsonData['RutaImagen'] = $('#RutaImagenManual').val();
        jsonData['NombreDigitalizador'] = $('#NombreDigitalizadorManual').val();

        // 🧩 Bloquear botón y mostrar carga
        $btn.prop("disabled", true);
        if (window.Swal) {
          Swal.fire({
            title: "Guardando...",
            text: "Por favor espere un momento.",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
          });
        }

        // 🚀 Enviar datos vía AJAX
        $.ajax({
          url: endpoint,
          type: "POST",
          dataType: "json",
          data: jsonData,
          headers: { "X-Requested-With": "XMLHttpRequest" },
          timeout: 15000, // 15 segundos

          complete: function () {
            $btn.prop("disabled", false);
            $btn.data("sending", false);
          },

          success: function (res) {
            Swal.close();
            console.log("📨 Respuesta del servidor:", res);

            if (res.status === "ok") {
              const msg = res.id_ind
                ? `Bautizo registrado correctamente. ID asignado: ${res.id_ind}`
                : res.msg || "Bautizo registrado correctamente.";
              notify("success", "Éxito", msg);
              resetFormularioCompleto(); // 🔄 Resetear todo el formulario y sesión manual
            } else if (res.status === "error") {
              let msg = res.msg || "Error desconocido.";
              if (msg.includes("Duplicate entry"))
                msg = "El ID del bautizado ya existe en la base de datos.";
              notify("error", "Error", msg);
            } else {
              notify("error", "Error inesperado", "Respuesta no válida del servidor.");
            }
          },

          error: function (xhr, status, error) {
            Swal.close();
            console.error("❌ Error AJAX:", status, error, xhr.responseText);
            let msg = "No se pudo conectar con el servidor.";
            if (status === "timeout")
              msg = "La conexión tardó demasiado. Intente nuevamente.";
            notify("error", "Error del servidor", msg);
          },
        });
      });

      /* =========================================
         6. LÓGICA DE CARGA DE IMAGEN MANUAL
         ========================================= */
      let manualUploadSessionId = null;
      let manualUploadPollingInterval = null;

      // Generar un ID de sesión único para la comunicación
      function generateSessionId() {
          return 'manual_' + Date.now() + '_' + Math.random().toString(36).substring(2, 8);
      }

      $('#btnActivarRecepcion').click(function() {
          console.log("DEBUG: btnActivarRecepcion clicked."); // Confirm button click
          const digitalizador = $('#digitalizadorNombreManual').val().trim();
          if (!digitalizador) {
              notify('warning', 'Digitalizador Requerido', 'Por favor, ingrese el nombre del digitalizador.');
              return;
          }

          if (manualUploadSessionId) {
              $.post('?controller=sacrej&action=api_finalizar_sesion_manual', { session_id: manualUploadSessionId });
          }

          manualUploadSessionId = generateSessionId();
          $.post('?controller=sacrej&action=api_iniciar_sesion_manual', { session_id: manualUploadSessionId });
          
          const serverIp = '<?php $serverIp = $_SERVER["SERVER_ADDR"] ?? ""; if ($serverIp === "127.0.0.1" || $serverIp === "::1") { $names = array_filter([gethostname(), php_uname('n')]); foreach ($names as $name) { $resolved = gethostbyname($name); if (filter_var($resolved, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && !in_array($resolved, ["127.0.0.1", "::1"], true)) { $serverIp = $resolved; break; } } } echo $serverIp; ?>';
          const hostname = window.location.hostname;
          const isLoopbackHost = hostname === 'localhost' || hostname === '127.0.0.1' || hostname === '::1';
          const useIpHost = serverIp && isLoopbackHost && serverIp !== '127.0.0.1' && serverIp !== '::1';
          const hostForLink = useIpHost ? serverIp : hostname;
          const uploadUrl = `${window.location.protocol}//${hostForLink}${window.location.pathname}?controller=sacrej&action=vista_cliente_manual_upload&session_id=${manualUploadSessionId}&digitalizador=${encodeURIComponent(digitalizador)}`;
          
          // 🐛 DEBUG: Log para verificar si QRCode está disponible y la URL generada
          console.log("DEBUG: QRCode library status at button click:", typeof QRCode !== 'undefined');
          console.log("DEBUG: Generated upload URL:", uploadUrl);
          
          if (useIpHost) {
              $('#manualUploadHelp').text('Accesible desde el teléfono usando la IP del servidor: ' + uploadUrl);
          } else if (isLoopbackHost) {
              $('#manualUploadHelp').text('Para usar desde el móvil, reemplace localhost/::1 por la IP local de este PC (ej. 192.168.x.x) si está en la misma red.');
          } else {
              $('#manualUploadHelp').text('La URL es accesible desde la red local.');
          }

          $('#manualUploadLink').attr('href', uploadUrl).text(uploadUrl);
          // 🆕 Asegurarse de que el área del QR sea visible ANTES de generarlo
          $('#manualUploadArea').removeClass('hidden').show(); 
          $('#qrcode').removeClass('hidden').show();
          
          // 🆕 Asegurarse de que el área del QR sea visible ANTES de generarlo
          $('#manualUploadArea').removeClass('hidden');
          $('#qrcode').empty();

          const qrcodeElement = document.getElementById("qrcode");
          if (typeof QRCode === 'undefined') {
              console.error("ERROR: QRCode library is not loaded. Make sure view/js/qrcode.min.js is accessible.");
              notify('error', 'Error QR', 'La librería de QR no se cargó correctamente. Verifique la consola para más detalles.');
          } else if (!qrcodeElement) {
              console.error("ERROR: QR code target element #qrcode not found.");
              notify('error', 'Error QR', 'El elemento donde se debe mostrar el QR no se encontró. Verifique el HTML.');
          } else {
              console.log("DEBUG: Attempting to generate QR code into #qrcode element.");
              // 🐛 DEBUG: Verificar si el elemento #qrcode está visible antes de renderizar
              console.log("DEBUG: #qrcode element visibility before render:", $(qrcodeElement).is(':visible'));
              
              // Intentar generar el QR
              try {
                  const qr = new QRCode(qrcodeElement, {
                      text: uploadUrl,
                      width: 128,
                      height: 128,
                      typeNumber: 0,
                      colorDark : "#000000",
                      colorLight : "#ffffff",
                      correctLevel : QRCode.CorrectLevel.H
                  });
              } catch (qrError) {
                  console.error("ERROR: Failed to instantiate QRCode:", qrError);
                  notify('error', 'Error QR', 'Hubo un error al intentar generar el código QR. Revise la consola.');
              }
              
              // 🐛 DEBUG: Verificar si el QR se renderizó y es visible después de un pequeño retraso
              setTimeout(() => {
                  const qrCanvas = qrcodeElement.querySelector('canvas');
                  if (qrCanvas) {
                      console.log("DEBUG: QR Canvas dimensions (width, height):", qrCanvas.offsetWidth, qrCanvas.offsetHeight);
                      if (qrCanvas.offsetWidth === 0 || qrCanvas.offsetHeight === 0 || $(qrCanvas).is(':hidden')) {
                          console.warn("WARNING: QR code canvas is rendered but has zero dimensions. Check CSS visibility/display properties.");
                      } else {
                          console.log("DEBUG: QR code canvas is visible and has dimensions.");
                          notify('success', 'QR Generado', 'El código QR se ha generado correctamente.');
                      }
                  } else {
                      console.warn("WARNING: QRCode library did not render a canvas element inside #qrcode.");
                  }
              }, 100); // Pequeño retraso para asegurar que el DOM se actualice
          }

          $('#manualUploadSpinner').removeClass('hidden');
          $('#manualUploadStatus').removeClass('hidden').text('Esperando imagen...');
          $('#imagenManualPreview').addClass('hidden').hide();
          $('#btnVerImagenManual').addClass('hidden').hide();
          $('#RutaImagenManual').val('');
          $('#NombreDigitalizadorManual').val('');

          // Iniciar polling
          if (manualUploadPollingInterval) clearInterval(manualUploadPollingInterval);
          manualUploadPollingInterval = setInterval(checkManualUploadStatus, 1000); // Cada 1 segundo
      });

      function checkManualUploadStatus() {
          if (!manualUploadSessionId) return;

          $.post('?controller=sacrej&action=api_check_manual_upload_status', { session_id: manualUploadSessionId }, function(res) {
              if (res.status === 'ready') {
                  clearInterval(manualUploadPollingInterval);
                  manualUploadPollingInterval = null;

                  const data = res.data;
                  $('#RutaImagenManual').val(data.ruta);
                  $('#NombreDigitalizadorManual').val(data.digitalizador);

                  // Mostrar preview de la imagen
                  let visorUrl = data.ruta.includes('.dat') ? `controller/visor.php?img=${encodeURIComponent(data.ruta)}` : data.ruta;
                  $('#previewImg').attr('src', visorUrl);
                  $('#btnVerImagenManual').attr('href', visorUrl);
                  $('#btnVerImagenManual').removeClass('hidden').show(); // Mostrar el botón
                  $('#previewDigitalizador').text(data.digitalizador);
                  $('#imagenManualPreview').removeClass('hidden').show();

                  $('#manualUploadArea').addClass('hidden').hide(); 
                  $('#manualUploadSpinner').addClass('hidden').hide();
                  $('#manualUploadStatus').addClass('hidden').text('').hide(); 

                  notify('success', 'Imagen Recibida', 'La imagen del folio ha sido cargada exitosamente.');
              }
          }, 'json').fail(function() {
              // Manejar errores de conexión si es necesario, pero no detener el polling
              console.warn('Error al verificar estado de carga manual.');
          });
      }

      $('#btnQuitarImagenManual').click(function() {
          const ruta = $('#RutaImagenManual').val();
          
          if (manualUploadSessionId) {
              $.post('?controller=sacrej&action=api_resetear_subida_manual', { 
                  session_id: manualUploadSessionId,
                  ruta: ruta 
              }, function(res) {
                  if (res.status === 'ok') {
                      // Limpiar valores y previsualización
                      $('#RutaImagenManual').val('');
                      $('#NombreDigitalizadorManual').val('');
                      $('#imagenManualPreview').addClass('hidden').hide();
                      $('#previewImg').attr('src', '');
                      $('#btnVerImagenManual').addClass('hidden').hide(); // Ocultar el botón
                      $('#btnVerImagenManual').attr('href', '#');
                      $('#previewDigitalizador').text('');

                      // Restaurar UI de espera para recibir la nueva imagen
                      $('#manualUploadArea').removeClass('hidden').show();
                      $('#manualUploadSpinner').removeClass('hidden').show();
                      $('#manualUploadStatus').removeClass('hidden').text('Esperando nueva imagen...').show();
                      
                      // Asegurar que el polling siga corriendo
                      if (!manualUploadPollingInterval) {
                          manualUploadPollingInterval = setInterval(checkManualUploadStatus, 1000);
                      }
                  }
              }, 'json');
          }
      });

      // 🔄 Función para resetear completamente el formulario y la sesión de carga manual
      function resetFormularioCompleto() {
          // 1. Resetear campos del formulario principal
          $("#formBautizo")[0].reset();
          $("#IdInd").val("");
          $('#digitalizadorNombreManual').val('');

          // 2. Finalizar sesión manual en el servidor si existe
          if (manualUploadSessionId) {
              $.post('?controller=sacrej&action=api_finalizar_sesion_manual', { session_id: manualUploadSessionId });
              manualUploadSessionId = null;
          }

          // 3. Detener el sondeo (polling) y ocultar UI de carga
          if (manualUploadPollingInterval) {
              clearInterval(manualUploadPollingInterval);
              manualUploadPollingInterval = null;
          }
          $('#manualUploadArea').addClass('hidden').hide();
          $('#qrcode').empty();
          $('#manualUploadSpinner').addClass('hidden').hide();
          $('#manualUploadStatus').addClass('hidden').text('').hide();
          $('#manualUploadLink').attr('href', '#').text('');
          $('#manualUploadHelp').text('');

          // 4. Limpiar datos de imagen y previsualización
          $('#RutaImagenManual').val('');
          $('#NombreDigitalizadorManual').val('');
          $('#imagenManualPreview').addClass('hidden').hide();
          $('#previewImg').attr('src', '');
          $('#btnVerImagenManual').addClass('hidden').hide().attr('href', '#');
          $('#previewDigitalizador').text('');
      }

      // Alias para compatibilidad si se llama desde otros procesos
      window.fullManualImageCleanup = resetFormularioCompleto;

      // 🛡️ Finalizar sesión en el servidor si se sale de la vista o se recarga
      $(window).on('beforeunload', function() {
          if (manualUploadSessionId) {
              const data = new URLSearchParams();
              data.append('session_id', manualUploadSessionId);
              navigator.sendBeacon('?controller=sacrej&action=api_finalizar_sesion_manual', data);
          }
      });

    });
  })();
</script>
