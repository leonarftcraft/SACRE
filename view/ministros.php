<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<div class="container mt-4">
  <h4 class="text-center mb-4">Gestión de Ministros Celebrantes</h4>

  <!-- 🔍 Buscador -->
  <div class="mb-3">
    <input type="text" id="buscarMinistro" class="form-control" placeholder="Buscar ministro...">
  </div>

  <!-- 📋 Tabla -->
  <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle" id="tablaMinistros">
      <thead class="table-dark text-center">
        <tr>
          <th>Código</th>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Jerarquía</th>
        </tr>
      </thead>
      <tbody id="tbodyMinistros">
        <?php
        if (!isset($this) || !property_exists($this, 'model')) {
          echo "<tr><td colspan='4' class='text-center text-danger'>Error: Vista cargada sin acceso al modelo.</td></tr>";
        } else {
          $datos = $this->model->obtener_ministros_con_jerarquia(); // ✅ Usar la consulta optimizada con JOIN
          if ($datos && $datos->num_rows > 0):
            while ($fila = $datos->fetch_assoc()):
        ?>
        <tr>
          <td class="text-center"><?= htmlspecialchars($fila['IdMinCel']) ?></td>
          <td><?= htmlspecialchars($fila['Nom']) ?></td>
          <td><?= htmlspecialchars($fila['Ape']) ?></td>
          <td><?= htmlspecialchars($fila['NomJer'] ?? '—') ?></td>
        </tr>
        <?php
            endwhile;
          else:
        ?>
        <tr><td colspan="4" class="text-center text-muted">No hay ministros registrados.</td></tr>
        <?php
          endif;
        }
        ?>
      </tbody>
    </table>
  </div>

  <div class="text-end">
    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalMinistro">
      <i class="bi bi-plus-circle"></i> Agregar Ministro
    </button>
  </div>
</div>

<!-- 🟦 Modal para registrar nuevo ministro -->
<div class="modal fade" id="modalMinistro" tabindex="-1" aria-labelledby="modalMinistroLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formMinistro" onsubmit="return false;">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalMinistroLabel">Registrar Ministro</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nombre:</label>
            <input type="text" name="Nom" id="Nom" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Apellido:</label>
            <input type="text" name="Ape" id="Ape" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Jerarquía:</label>
            <select name="CodJer" id="CodJer" class="form-select" required>
              <option value="">Seleccione...</option>
              <?php
              $jerarquias = $this->model->obtener_todos("jerarquia_ministro");
              if ($jerarquias && $jerarquias->num_rows > 0):
                while ($j = $jerarquias->fetch_assoc()):
                  echo "<option value='{$j['CodJer']}'>" . htmlspecialchars($j['NomJer']) . "</option>";
                endwhile;
              else:
                echo "<option value=''>No hay jerarquías registradas</option>";
              endif;
              ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" id="btnGuardarMinistro" class="btn btn-success">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function() {

  // ⚡ Comprobación inicial
  console.log("📄 ministros.php cargado correctamente");

  // Si jQuery no está cargado aún, espera hasta que lo esté
  function whenReady(fn) {
    if (window.jQuery) fn();
    else setTimeout(() => whenReady(fn), 200);
  }

  // 🧭 Ruta del endpoint
  const endpoint = "index.php?controller=sacrej&action=agregar_ministro";

  // 🔧 Notificaciones
  function notify(type, title, text) {
    if (window.Swal && Swal.fire) {
      Swal.fire({ icon: type, title, text });
    } else {
      alert(title + "\n" + text);
    }
  }

  // 🪄 Ocultar modal
  function hideModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
    modal.hide();
  }

  // Ejecutar cuando jQuery esté listo
  whenReady(() => {
    console.log("✅ jQuery detectado, activando scripts...");

    // 🔍 Buscador
    $("#buscarMinistro").on("keyup", function() {
      const valor = $(this).val().toLowerCase();
      $("#tablaMinistros tbody tr").each(function() {
        $(this).toggle($(this).text().toLowerCase().includes(valor));
      });
    });

    // 💾 Guardar ministro
    $("#btnGuardarMinistro").off("click").on("click", function() {
      const nom = $("#Nom").val().trim();
      const ape = $("#Ape").val().trim();
      const codJer = $("#CodJer").val();

      console.log("📤 Enviando datos:", { Nom: nom, Ape: ape, CodJer: codJer });

      if (!nom || !ape || !codJer) {
        notify("warning", "Campos vacíos", "Por favor completa todos los campos.");
        return;
      }

      $.ajax({
        url: endpoint,
        type: "POST",
        dataType: "json",
        data: { Nom: nom, Ape: ape, CodJer: codJer },
        headers: { "X-Requested-With": "XMLHttpRequest" },
        beforeSend: function() {
          if (window.Swal) Swal.fire({
            title: "Guardando...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
          });
        },
        success: function(res) {
          if (window.Swal) Swal.close();
          console.log("📨 Respuesta del servidor:", res);
          if (res && res.status === "ok") {
            notify("success", "Éxito", res.msg || "Ministro registrado correctamente.");
            const nombreJer = $("#CodJer option:selected").text();
            $("#tbodyMinistros").append(`
              <tr>
                <td class="text-center">—</td>
                <td>${nom}</td>
                <td>${ape}</td>
                <td>${nombreJer}</td>
              </tr>
            `);
            $("#formMinistro")[0].reset();
            hideModal("modalMinistro");
          } else {
            notify("error", "Error", res && res.msg ? res.msg : "No se pudo registrar el ministro.");
          }
        },
        error: function(xhr, status, error) {
          if (window.Swal) Swal.close();
          console.error("❌ Error AJAX:", status, error, xhr.responseText);
          notify("error", "Error del servidor", "No se pudo conectar con el servidor.");
        }
      });
    });
  });

})();
</script>
