<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<div class="container mt-4">
  <h4 class="text-center mb-4">Gestión de Tipos de Celebración</h4>

  <!-- 🔍 Buscador -->
  <div class="mb-3">
    <input type="text" id="buscarCelebracion" class="form-control" placeholder="Buscar celebración...">
  </div>

  <!-- 📋 Tabla -->
  <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle" id="tablaCelebraciones">
      <thead class="table-dark text-center">
        <tr>
          <th>ID</th>
          <th>Descripción</th>
        </tr>
      </thead>
      <tbody id="tbodyCelebraciones">
        <?php
        if (!isset($this) || !property_exists($this, 'model')) {
          echo "<tr><td colspan='2' class='text-center text-danger'>Vista cargada sin controlador/modelo.</td></tr>";
        } else {
          $datos = $this->model->obtener_todos("tipo_celebracion");
          if ($datos && $datos->num_rows > 0):
            while ($fila = $datos->fetch_assoc()):
        ?>
        <tr>
          <td class="text-center"><?= htmlspecialchars($fila['IdTip']) ?></td>
          <td><?= htmlspecialchars($fila['DesTip']) ?></td>
        </tr>
        <?php
            endwhile;
          else:
            echo "<tr><td colspan='2' class='text-center text-muted'>No hay celebraciones registradas.</td></tr>";
          endif;
        }
        ?>
      </tbody>
    </table>
  </div>

  <div class="text-end">
    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalCelebracion">
      <i class="bi bi-plus-circle"></i> Agregar Celebración
    </button>
  </div>
</div>

<!-- 🟦 Modal para agregar celebración -->
<div class="modal fade" id="modalCelebracion" tabindex="-1" aria-labelledby="modalCelebracionLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formCelebracion" onsubmit="return false;">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalCelebracionLabel">Registrar Celebración</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Descripción:</label>
            <input type="text" name="DesTip" id="DesTip" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" id="btnGuardarCelebracion" class="btn btn-success">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function() {

  console.log("📄 celebraciones.php cargado correctamente");

  // Esperar jQuery
  function whenReady(fn) {
    if (window.jQuery) fn();
    else setTimeout(() => whenReady(fn), 200);
  }

  const endpoint = "http://localhost/sacrej/index.php?controller=sacrej&action=agregar_celebracion";

  function notify(type, title, text) {
    if (window.Swal && Swal.fire) {
      Swal.fire({ icon: type, title, text });
    } else {
      alert(title + "\n" + text);
    }
  }

  function hideModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
    modal.hide();
  }

  whenReady(() => {
    console.log("✅ jQuery detectado, activando scripts de celebraciones...");

    // Buscador
    $("#buscarCelebracion").on("keyup", function() {
      const valor = $(this).val().toLowerCase();
      $("#tablaCelebraciones tbody tr").each(function() {
        $(this).toggle($(this).text().toLowerCase().includes(valor));
      });
    });

    // Guardar celebración
    $("#btnGuardarCelebracion").off("click").on("click", function() {
      const descripcion = $("#DesTip").val().trim();

      console.log("📤 Enviando datos:", { DesTip: descripcion });

      if (!descripcion) {
        notify("warning", "Campos vacíos", "Por favor ingrese la descripción.");
        return;
      }

      $.ajax({
        url: endpoint,
        type: "POST",
        dataType: "json",
        data: { DesTip: descripcion },
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
            notify("success", "Éxito", res.msg || "Celebración registrada correctamente.");
            $("#tbodyCelebraciones").append(`
              <tr>
                <td class="text-center">—</td>
                <td>${descripcion}</td>
              </tr>
            `);
            $("#formCelebracion")[0].reset();
            hideModal("modalCelebracion");
          } else {
            notify("error", "Error", res && res.msg ? res.msg : "No se pudo registrar la celebración.");
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
