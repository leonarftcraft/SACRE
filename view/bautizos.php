<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<div class="container mt-4">
  <h4 class="text-center mb-4">Registro de Bautizos</h4>

  <form id="formBautizo" onsubmit="return false;">
    <!-- 🟦 Encabezado -->
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Código de Celebración</label>
        <input type="number" id="IdCel" name="IdCel" class="form-control" min="1" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Libro N°</label>
        <input type="number" id="NumLib" name="NumLib" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Folio N°</label>
        <input type="number" id="NumFol" name="NumFol" class="form-control" required>
      </div>
    </div>

    <!-- 📘 Tipo de Celebración -->
    <div class="row g-3 mt-3">
      <div class="col-md-6">
        <label class="form-label">Tipo de Celebración</label>
        <select id="TipCel" name="TipCel" class="form-select" required>
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
        <input type="text" id="NomInd" name="NomInd" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Apellido(s)</label>
        <input type="text" id="ApeInd" name="ApeInd" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Sexo</label>
        <select id="SexInd" name="SexInd" class="form-select" required>
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
          <input type="text" id="NomMad" name="NomMad" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Apellido</label>
          <input type="text" id="ApeMad" name="ApeMad" class="form-control" required>
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
        <select id="FilInd" name="FilInd" class="form-select" required>
          <option value="">Seleccione…</option>
          <option value="1">Reconocido</option>
          <option value="2">Legítimo</option>
          <option value="3">Natural</option>
        </select>
      </div>
      <div class="col-md-5">
        <label class="form-label">Lugar de nacimiento</label>
        <input type="text" id="LugNacInd" name="LugNacInd" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Fecha de nacimiento</label>
        <input type="date" id="FecNacInd" name="FecNacInd" class="form-control" required>
      </div>
    </div>

    <!-- ✝️ Celebración -->
    <div class="row g-3 mt-3">
      <div class="col-md-3">
        <label class="form-label">Fecha del Bautizo</label>
        <input type="date" id="FechCel" name="FechCel" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Lugar de la Celebración</label>
        <input type="text" id="Lugar" name="Lugar" class="form-control" required>
      </div>
      <div class="col-md-5">
        <label class="form-label">Ministro Celebrante</label>
        <select id="IdMin" name="IdMin" class="form-select" required>
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
        <input type="text" id="Pad1Nom" name="Pad1Nom" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Apellido</label>
        <input type="text" id="Pad1Ape" name="Pad1Ape" class="form-control" required>
      </div>
      <!-- Sexo enviado oculto: 1 = Masculino (padrino) -->
      <input type="hidden" id="Pad1Sex" name="Pad1Sex" value="1">

      <div class="col-12"><strong>Datos de la Madrina</strong></div>

      <div class="col-md-4">
        <label class="form-label">Nombre</label>
        <input type="text" id="Pad2Nom" name="Pad2Nom" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Apellido</label>
        <input type="text" id="Pad2Ape" name="Pad2Ape" class="form-control" required>
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

    <!-- Botones -->
    <div class="text-end mt-4">
      <button type="reset" class="btn btn-secondary">Limpiar</button>
      <button type="button" id="btnGuardarBautizo" class="btn btn-success">Guardar</button>
    </div>
  </form>
</div>

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

      const endpoint = "http://localhost/sacrej/index.php?controller=sacrej&action=agregar_bautizo";

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
      // LugNacInd, Lugar, NotMar
      ["#LugNacInd", "#Lugar", "#NotMar"].forEach(sel => {
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

        // 🧩 Antirrebote: evitar doble clic
        if ($btn.data("sending")) return;
        $btn.data("sending", true);

        // Validar ID generado
        const id = $("#IdInd").val();
        if (!id) {
          notify(
            "warning",
            "Falta información",
            "No se generó el ID del bautizado. Verifique nombre, apellido y fecha de nacimiento."
          );
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
              $("#formBautizo")[0].reset();
              $("#IdInd").val("");
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
    });
  })();
</script>
