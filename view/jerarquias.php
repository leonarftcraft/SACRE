<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<div class="container mt-4">
  <h4 class="text-center mb-4">Gestión de Jerarquías Eclesiásticas</h4>

  <!-- 🔍 Buscador -->
  <div class="mb-3">
    <input type="text" id="buscarJerarquia" class="form-control" placeholder="Buscar jerarquía...">
  </div>

  <!-- 📋 Tabla -->
  <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle" id="tablaJerarquia">
      <thead class="table-dark text-center">
        <tr>
          <th>Código</th>
          <th>Nombre</th>
          <th>Descripción</th>
        </tr>
      </thead>
      <tbody id="tbodyJerarquia">
        <?php
        if (!isset($this) || !property_exists($this, 'model')) {
          echo "<tr><td colspan='3' class='text-center text-danger'>Vista cargada sin controlador/modelo. Cárgala desde SacrejController->vista_jerarquias().</td></tr>";
        } else {
          $datos = $this->model->obtener_todos("jerarquia_ministro");
          if ($datos && $datos->num_rows > 0):
            while ($fila = $datos->fetch_assoc()):
        ?>
        <tr>
          <td class="text-center"><?= htmlspecialchars($fila['CodJer'] ?? '') ?></td>
          <td><?= htmlspecialchars($fila['NomJer'] ?? '') ?></td>
          <td><?= htmlspecialchars($fila['DesJer'] ?? '') ?></td>
        </tr>
        <?php
            endwhile;
          else:
        ?>
        <tr><td colspan="3" class="text-center text-muted">No hay jerarquías registradas.</td></tr>
        <?php
          endif;
        }
        ?>
      </tbody>
    </table>
  </div>

  <div class="text-end">
    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalJerarquia">
      <i class="bi bi-plus-circle"></i> Agregar Jerarquía
    </button>
  </div>
</div>

<!-- 🟦 Modal -->
<div class="modal fade" id="modalJerarquia" tabindex="-1" aria-labelledby="modalJerarquiaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formJerarquia" onsubmit="return false;">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalJerarquiaLabel">Registrar Jerarquía</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label" for="NomJer">Nombre:</label>
            <input type="text" name="NomJer" id="NomJer" class="form-control" autocomplete="off" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="DesJer">Descripción:</label>
            <textarea name="DesJer" id="DesJer" class="form-control" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" id="btnGuardarJerarquia" class="btn btn-success">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// ——— utilidades ———
function notify(type, title, text) {
  if (window.Swal && typeof Swal.fire === "function") {
    Swal.fire({ icon: type, title, text });
  } else {
    alert((title ? title + ":\n" : "") + (text || ""));
  }
}

function computeEndpoint() {
  // Ruta base hasta la carpeta del proyecto
  // Ej: http://localhost/sacrej/  ó http://localhost/sacrej_1.0/
  const { origin, pathname } = window.location;
  // Si estás en .../index.php?... => base = .../
  // Si estás en .../view/jerarquias.php => base = .../
  let base = pathname;
  // quitar /view/... o /index.php...
  base = base.replace(/view\/.*/i, "");
  base = base.replace(/index\.php.*/i, "");
  if (!base.endsWith("/")) base += "/";
  const endpoint = origin + base + "index.php?controller=sacrej&action=agregar_jerarquia";
  console.log("🔗 Endpoint calculado:", endpoint);
  return endpoint;
}

function appendRow(nomJer, desJer) {
  const tbody = document.getElementById("tbodyJerarquia");
  const tr = document.createElement("tr");
  tr.innerHTML = `
    <td class="text-center">—</td>
    <td>${document.createElement("div").appendChild(document.createTextNode(nomJer)).parentNode.innerHTML}</td>
    <td>${document.createElement("div").appendChild(document.createTextNode(desJer)).parentNode.innerHTML}</td>
  `;
  tbody.appendChild(tr);
}

function hideModalById(id) {
  const el = document.getElementById(id);
  if (!el) return;
  try {
    const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
    modal.hide();
  } catch(e) { /* sin bootstrap no pasa nada */ }
}

// ——— Fallback SIN jQuery ———
function bindVanilla() {
  console.log("🟣 Activado modo Vanilla JS");
  const buscar = document.getElementById("buscarJerarquia");
  if (buscar) {
    buscar.addEventListener("keyup", function() {
      const valor = this.value.toLowerCase();
      document.querySelectorAll("#tablaJerarquia tbody tr").forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(valor) ? "" : "none";
      });
    });
  }

  const btn = document.getElementById("btnGuardarJerarquia");
  if (btn) {
    btn.addEventListener("click", async function() {
      const nomJer = (document.getElementById("NomJer").value || "").trim();
      const desJer = (document.getElementById("DesJer").value || "").trim();

      console.log("🟪 Click (Vanilla) — datos:", { NomJer: nomJer, DesJer: desJer });

      if (!nomJer || !desJer) {
        notify("warning", "Campos vacíos", "Por favor completa todos los campos.");
        return;
      }

      const endpoint = computeEndpoint();
      try {
        const fd = new FormData();
        fd.append("NomJer", nomJer);
        fd.append("DesJer", desJer);

        const res = await fetch(endpoint, { method: "POST", body: fd, headers: { "X-Requested-With": "XMLHttpRequest" }});
        const txt = await res.text();
        console.log("📩 Respuesta texto:", txt);
        let data;
        try { data = JSON.parse(txt); } catch(e) { data = null; }

        if (res.ok && data && data.status === "ok") {
          notify("success", "Éxito", data.msg || "Jerarquía registrada.");
          appendRow(nomJer, desJer);
          document.getElementById("formJerarquia").reset();
          hideModalById("modalJerarquia");
        } else {
          notify("error", "Error", (data && data.msg) ? data.msg : "No se pudo registrar la jerarquía.");
        }
      } catch (err) {
        console.error("❌ Error fetch:", err);
        notify("error", "Error del servidor", "No se pudo conectar con el servidor.");
      }
    });
  }
}

// ——— Con jQuery ———
function bindWithjQuery($) {
  console.log("🟢 Activado modo jQuery");
  $("#buscarJerarquia").on("keyup", function() {
    const valor = $(this).val().toLowerCase();
    $("#tablaJerarquia tbody tr").each(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(valor) > -1);
    });
  });

  $("#btnGuardarJerarquia").off("click").on("click", function() {
    const nomJer = $("#NomJer").val().trim();
    const desJer = $("#DesJer").val().trim();
    console.log("🟩 Click (jQuery) — datos:", { NomJer: nomJer, DesJer: desJer });

    if (!nomJer || !desJer) {
      notify("warning", "Campos vacíos", "Por favor completa todos los campos.");
      return;
    }

    const endpoint = computeEndpoint();
    $.ajax({
      url: endpoint,
      type: "POST",
      dataType: "json",
      data: { NomJer: nomJer, DesJer: desJer },
      headers: { "X-Requested-With": "XMLHttpRequest" },
      beforeSend: function() { if (window.Swal) Swal.showLoading(); },
      success: function(res) {
        if (window.Swal) Swal.close();
        console.log("📨 Respuesta JSON:", res);
        if (res && res.status === "ok") {
          notify("success", "Éxito", res.msg || "Jerarquía registrada.");
          appendRow(nomJer, desJer);
          $("#formJerarquia")[0].reset();
          hideModalById("modalJerarquia");
        } else {
          notify("error", "Error", (res && res.msg) ? res.msg : "No se pudo registrar la jerarquía.");
        }
      },
      error: function(xhr, status, error) {
        if (window.Swal) Swal.close();
        console.error("❌ Error AJAX:", status, error, xhr && xhr.responseText);
        notify("error", "Error del servidor", "No se pudo conectar con el servidor.");
      }
    });
  });
}

// ——— Arranque ———
(function start() {
  console.log("✅ jerarquias.php cargado");
  if (window.jQuery) bindWithjQuery(window.jQuery);
  else bindVanilla();
})();
</script>
