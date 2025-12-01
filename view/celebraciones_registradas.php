<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($this) || !property_exists($this, 'model')) {
    echo "<div class='alert alert-danger'>No se puede acceder al modelo desde la vista.</div>";
    return;
}

// 🔹 Obtenemos las celebraciones de tipo Bautizo
$celebraciones = $this->model->obtener_celebraciones_bautizo();

// 🔹 Ministros firmantes (usuarios con rol de ministro: 20 / 200)
$ministrosFirmantes = $this->model->obtener_ministros_firmantes();
?>

<div class="container mt-4">
  <h4 class="text-center mb-4">Celebraciones Registradas (Bautizos)</h4>

  <!-- 🔍 Buscador -->
  <div class="row mb-3">
    <div class="col-md-6">
      <input type="text" id="buscarCelebracion" class="form-control" placeholder="Buscar por nombre, apellido o fecha...">
    </div>
  </div>

  <!-- 📋 Tabla -->
  <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle" id="tablaCelebraciones">
      <thead class="table-dark text-center">
        <tr>
          <th>#</th>
          <th>Nombre del Bautizado</th>
          <th>Fecha de Nacimiento</th>
          <th>Fecha de Bautizo</th>
          <th>Ficha</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $i = 1;
        if ($celebraciones && $celebraciones->num_rows > 0):
          while ($fila = $celebraciones->fetch_assoc()):
            $nom = $fila['NomInd'] ?? '';
            $ape = $fila['ApeInd'] ?? '';
            $nombreCompleto = htmlspecialchars(trim($nom . ' ' . $ape));
            if ($nombreCompleto === '') {
                $nombreCompleto = '—Sin individuo asociado—';
            }

            $fecNac  = !empty($fila['FecNacInd']) ? date("d/m/Y", strtotime($fila['FecNacInd'])) : "";
            $fecBaut = !empty($fila['FechCel'])   ? date("d/m/Y", strtotime($fila['FechCel']))   : "";
        ?>
          <tr data-idcel="<?= $fila['IdCel']; ?>">
            <td class="text-center"><?= $i++; ?></td>
            <td><?= $nombreCompleto; ?></td>
            <td class="text-center"><?= $fecNac; ?></td>
            <td class="text-center"><?= $fecBaut; ?></td>
            <td class="text-center">
              <button 
                class="btn btn-sm btn-info btnFicha" 
                type="button"
                data-idcel="<?= $fila['IdCel']; ?>"
                title="Ver ficha de celebración">
                <img src="view/images/perfil.png" alt="Ficha" style="width:18px;height:18px;">
              </button>
            </td>
          </tr>
        <?php
          endwhile;
        else:
        ?>
          <tr>
            <td colspan="5" class="text-center text-muted">
              No hay celebraciones registradas.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- 🔢 Paginación -->
  <div class="d-flex justify-content-between align-items-center mt-3">
    <div>
      <label class="form-label mb-0">
        Mostrar
        <select id="registrosPorPagina" class="form-select d-inline-block w-auto ms-1 me-1">
          <option value="10" selected>10</option>
          <option value="25">25</option>
          <option value="50">50</option>
        </select>
        registros
      </label>
    </div>
    <div class="d-flex align-items-center">
      <button class="btn btn-outline-secondary btn-sm me-2" id="btnPrev">Anterior</button>
      <span id="paginaInfo" class="small"></span>
      <button class="btn btn-outline-secondary btn-sm ms-2" id="btnNext">Siguiente</button>
    </div>
  </div>
</div>

<!-- ======================= MODAL FICHA ======================= -->
<div class="modal fade" id="modalFicha" tabindex="-1" aria-labelledby="modalFichaLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="modalFichaLabel">Ficha de Bautizo</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">

        <!-- Simulación del formato de la partida -->
        <div class="mb-2">
          <strong>Nombre:</strong>
          <span id="fichaNombre"></span>
        </div>

        <div class="mb-2">
          <strong>Padres:</strong>
          <span id="fichaPadres"></span>
        </div>

        <div class="mb-2">
          <strong>Filiación:</strong>
          <span id="fichaFiliacion"></span>
        </div>

        <div class="mb-2">
          <strong>Nacido(a) en:</strong>
          <span id="fichaLugarNac"></span>
        </div>

        <div class="mb-2">
          <strong>Fecha de nacimiento:</strong>
          <span id="fichaFechaNac"></span>
        </div>

        <div class="mb-2">
          <strong>Bautizado(a) el:</strong>
          <span id="fichaFechaBaut"></span>
          <strong> en:</strong>
          <span id="fichaLugarBaut"></span>
        </div>

        <div class="mb-2">
          <strong>Padrinos:</strong>
          <span id="fichaPadrinos"></span>
        </div>

        <div class="mb-2">
          <strong>Ministro:</strong>
          <span id="fichaMinistro"></span>
        </div>

        <div class="mb-2">
          <strong>Registro Civil Nº:</strong>
          <span id="fichaRegCiv"></span>
        </div>

        <div class="mb-2">
          <strong>Observaciones:</strong>
          <span id="fichaObs"></span>
        </div>

        <hr>
        <div class="text-end small text-muted mb-3">
          <span id="fichaTipo"></span> · Libro: <span id="fichaLibro"></span> · Folio: <span id="fichaFolio"></span>
        </div>

        <!-- ================= Ministro firmante ================= -->
        <div class="row mt-2">
          <div class="col-md-8">
            <label for="ministroFirmante" class="form-label">
              <strong>Ministro que firma el certificado</strong>
            </label>
            <select id="ministroFirmante" class="form-select">
              <option value="">Seleccione un ministro...</option>
              <?php if ($ministrosFirmantes && $ministrosFirmantes->num_rows > 0): ?>
                <?php while ($min = $ministrosFirmantes->fetch_assoc()): ?>
                  <option value="<?= $min['IdUsu']; ?>">
                    <?= htmlspecialchars($min['NomUsu'] . ' ' . $min['ApeUsu']); ?>
                  </option>
                <?php endwhile; ?>
              <?php endif; ?>
            </select>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btnGenerarCertificado">
          Generar certificado
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {

  /* =============== Variables de paginación =============== */
  let filas = $("#tablaCelebraciones tbody tr");
  let pageSize = parseInt($("#registrosPorPagina").val(), 10);
  let currentPage = 1;

  function getFilteredRows() {
    const search = $("#buscarCelebracion").val().toLowerCase();
    return filas.filter(function () {
      return $(this).text().toLowerCase().indexOf(search) > -1;
    });
  }

  function renderTabla() {
    const filtradas = getFilteredRows();
    const total = filtradas.length;
    const totalPages = Math.max(1, Math.ceil(total / pageSize));

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    // Ocultar todas
    filas.hide();

    const start = (currentPage - 1) * pageSize;
    const end   = start + pageSize;

    filtradas.slice(start, end).show();

    $("#paginaInfo").text(total === 0 ? "0 / 0" : (currentPage + " / " + totalPages));
    $("#btnPrev").prop("disabled", currentPage === 1 || total === 0);
    $("#btnNext").prop("disabled", currentPage === totalPages || total === 0);
  }

  /* =============== Buscador =============== */
  $("#buscarCelebracion").on("keyup", function() {
    currentPage = 1;
    renderTabla();
  });

  /* =============== Cambio de tamaño de página =============== */
  $("#registrosPorPagina").on("change", function () {
    pageSize = parseInt($(this).val(), 10);
    currentPage = 1;
    renderTabla();
  });

  /* =============== Botones Anterior / Siguiente =============== */
  $("#btnPrev").on("click", function () {
    currentPage--;
    renderTabla();
  });

  $("#btnNext").on("click", function () {
    currentPage++;
    renderTabla();
  });

  /* =============== Utilidad para formatear fecha =============== */
  function formatearFecha(iso) {
    if (!iso) return "";
    const partes = iso.split("-");
    if (partes.length !== 3) return iso;
    return partes[2] + "/" + partes[1] + "/" + partes[0];
  }

  function textoFiliacion(cod) {
    if (cod == 1 || cod === "1") return "Reconocido";
    if (cod == 0 || cod === "0") return "No reconocido";
    return cod || "";
  }

  /* =============== Click en botón Ficha =============== */
  $(document).on("click", ".btnFicha", function() {
    const idCel = $(this).data("idcel");
    $("#modalFicha").data("idcel", idCel);

    // Reset select de ministro firmante cada vez que se abre la ficha
    $("#ministroFirmante").val("");

    $.post(
      "?controller=sacrej&action=detalle_celebracion",
      { idCel: idCel },
      function(res) {
        if (res.success) {
          const d = res.data;

          $("#fichaNombre").text(d.nombre_completo || "");
          $("#fichaPadres").text(d.padres || "");
          $("#fichaFiliacion").text(textoFiliacion(d.filiacion));
          $("#fichaLugarNac").text(d.lugar_nac || "");
          $("#fichaFechaNac").text(formatearFecha(d.fec_nac));
          $("#fichaFechaBaut").text(formatearFecha(d.fecha_bautizo));
          $("#fichaLugarBaut").text(d.lugar_bautizo || "");
          $("#fichaPadrinos").text(d.padrinos || "");
          $("#fichaMinistro").text(d.ministro || "");
          $("#fichaRegCiv").text(d.registro_civil || "");
          $("#fichaObs").text(d.observaciones || "");
          $("#fichaTipo").text(d.tipo_celebracion || "Bautizo");
          $("#fichaLibro").text(d.num_libro || "");
          $("#fichaFolio").text(d.num_folio || "");

          const modal = new bootstrap.Modal(document.getElementById("modalFicha"));
          modal.show();

        } else {
          const msg = res.error || "No se pudo cargar el detalle de la celebración.";
          if (window.Swal) Swal.fire("Error", msg, "error");
          else alert(msg);
        }
      },
      "json"
    ).fail(function() {
      const msg = "Error de conexión con el servidor.";
      if (window.Swal) Swal.fire("Error", msg, "error");
      else alert(msg);
    });
  });

  /* =============== Click en "Generar certificado" =============== */
  $("#btnGenerarCertificado").on("click", function () {
    const idCel = $("#modalFicha").data("idcel");
    const idMinistro = $("#ministroFirmante").val();

    if (!idCel) {
      if (window.Swal) Swal.fire("Aviso", "No se encontró el identificador de la celebración.", "warning");
      else alert("No se encontró el identificador de la celebración.");
      return;
    }

    if (!idMinistro) {
      if (window.Swal) Swal.fire("Aviso", "Debe seleccionar el ministro que firmará el certificado.", "warning");
      else alert("Debe seleccionar el ministro que firmará el certificado.");
      return;
    }

    const url = `?controller=sacrej&action=generar_certificado&idCel=${encodeURIComponent(idCel)}&idUsu=${encodeURIComponent(idMinistro)}`;
    window.open(url, "_blank");
  });

  // Render inicial
  renderTabla();
});
</script>
