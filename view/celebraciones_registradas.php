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

// 🔹 Ministros celebrantes (para el select de edición)
$ministrosCelebrantes = $this->model->obtener_todos("ministro_celebrante");

// 🔹 Jerarquías (para el modal de nuevo ministro)
$jerarquias = $this->model->obtener_todos("jerarquia_ministro");
?>

<div class="container mt-4">
  <h4 class="text-center mb-4">Celebraciones Registradas (Bautizos)</h4>

  <!-- 🔍 Filtros -->
  <div class="card card-body bg-light mb-4 shadow-sm">
      <div class="row g-3">
          <div class="col-md-3">
              <label class="form-label small fw-bold">Fecha Nacimiento (Rango)</label>
              <div class="input-group input-group-sm">
                  <input type="date" id="filtroFecNacIni" class="form-control" title="Desde">
                  <span class="input-group-text">-</span>
                  <input type="date" id="filtroFecNacFin" class="form-control" title="Hasta">
              </div>
          </div>
          <div class="col-md-3">
              <label class="form-label small fw-bold">Fecha Bautizo (Rango)</label>
              <div class="input-group input-group-sm">
                  <input type="date" id="filtroFecBautIni" class="form-control" title="Desde">
                  <span class="input-group-text">-</span>
                  <input type="date" id="filtroFecBautFin" class="form-control" title="Hasta">
              </div>
          </div>
          <div class="col-md-2">
              <label class="form-label small fw-bold">Sexo</label>
              <select id="filtroSexo" class="form-select form-select-sm">
                  <option value="">Todos</option>
                  <option value="1">Masculino</option>
                  <option value="2">Femenino</option>
              </select>
          </div>
          <div class="col-md-4">
              <label class="form-label small fw-bold">Buscador General</label>
              <input type="text" id="buscarCelebracion" class="form-control form-control-sm" placeholder="Nombre, apellido, ministro...">
          </div>
      </div>
  </div>

  <!-- 📋 Tabla -->
  <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle" id="tablaCelebraciones">
      <thead class="table-dark text-center">
        <tr>
          <th>#</th>
          <th>Nombre del Bautizado</th>
          <th>Sexo</th>
          <th>Fecha de Nacimiento</th>
          <th>Fecha de Bautizo</th>
          <th>Ministro Celebrante</th>
          <th>Acción</th>
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

            $fecNacRaw = $fila['FecNacInd'] ?? '';
            $fecBautRaw = $fila['FechCel'] ?? '';

            $fecNac  = (!empty($fecNacRaw) && $fecNacRaw !== '0000-00-00') ? date("d/m/Y", strtotime($fecNacRaw)) : "";
            $fecBaut = (!empty($fecBautRaw) && $fecBautRaw !== '0000-00-00') ? date("d/m/Y", strtotime($fecBautRaw))   : "";
            
            $sexoCode = $fila['SexInd'] ?? '';
            $sexoTexto = ($sexoCode == 1) ? 'Masculino' : (($sexoCode == 2) ? 'Femenino' : 'Otro');
            
            $ministro = htmlspecialchars(trim(($fila['MinNom'] ?? '') . ' ' . ($fila['MinApe'] ?? '')));
            $urlImagen = $fila['UrlArchivo'] ?? '';
        ?>
          <tr 
            data-idcel="<?= $fila['IdCel']; ?>"
            data-sexo="<?= $sexoCode; ?>"
            data-fecnac="<?= $fecNacRaw; ?>"
            data-fecbaut="<?= $fecBautRaw; ?>"
          >
            <td class="text-center"><?= $i++; ?></td>
            <td><?= $nombreCompleto; ?></td>
            <td class="text-center"><?= $sexoTexto; ?></td>
            <td class="text-center"><?= $fecNac; ?></td>
            <td class="text-center"><?= $fecBaut; ?></td>
            <td><?= $ministro; ?></td>
            <td class="text-center">
              <!-- Botón Ficha -->
              <button 
                class="btn btn-sm btn-info btnFicha me-1" 
                type="button"
                data-idcel="<?= $fila['IdCel']; ?>"
                title="Ver ficha de celebración">
                <img src="view/images/perfil.png" alt="Ficha" style="width:18px;height:18px;">
              </button>

              <!-- Botón Editar -->
              <button class="btn btn-sm btn-warning btnEditar me-1" type="button" data-idcel="<?= $fila['IdCel']; ?>" title="Editar Registro">
                  ✏️
              </button>

              <!-- Botón Ver Imagen (si existe) -->
              <?php if (!empty($urlImagen)): ?>
                  <a href="<?= htmlspecialchars($urlImagen); ?>" target="_blank" class="btn btn-sm btn-success" title="Ver Imagen del Acta">
                      📷
                  </a>
              <?php endif; ?>
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

        <div class="mb-2">
          <strong>Imagen del Acta:</strong>
          <div id="fichaImagenContainer" class="mt-1"></div>
          <div id="fichaDigitalizador" class="small text-muted fst-italic mt-1"></div>
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

<!-- ======================= MODAL EDICIÓN ======================= -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">Editar Registro de Bautizo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formEditarBautizo">
            <input type="hidden" name="Id" id="editId">
            <input type="hidden" name="IdCel" id="editIdCel">
            <input type="hidden" name="IdInd" id="editIdInd">

            <!-- Estatus -->
            <div class="mb-3">
                <label class="form-label fw-bold">Estatus del Acta</label>
                <select name="EstCel" id="editEstCel" class="form-select form-select-sm">
                    <option value="1">Estandar</option>
                    <option value="2">Caso Especial</option>
                    <option value="0">Nulo</option>
                </select>
            </div>

            <!-- Datos Celebración -->
            <h6 class="text-primary border-bottom pb-1">Datos de la Celebración</h6>
            <div class="row g-2 mb-3">
                <div class="col-md-3"><label class="small fw-bold">Libro</label><input type="number" name="NumLib" id="editNumLib" class="form-control form-control-sm" required data-was-required="true"></div>
                <div class="col-md-3"><label class="small fw-bold">Folio</label><input type="number" name="NumFol" id="editNumFol" class="form-control form-control-sm" required data-was-required="true"></div>
                <div class="col-md-3"><label class="small fw-bold">Fecha Bautizo</label><input type="date" name="FechCel" id="editFechCel" class="form-control form-control-sm" required data-was-required="true"></div>
                <div class="col-md-3"><label class="small fw-bold">Lugar</label><input type="text" name="Lugar" id="editLugar" class="form-control form-control-sm" required data-was-required="true"></div>
                <div class="col-md-6">
                    <label class="small fw-bold">Ministro</label>
                    <div class="input-group input-group-sm">
                        <select name="IdMin" id="editIdMin" class="form-select" required data-was-required="true">
                            <option value="">Seleccione...</option>
                            <?php 
                            // Usamos la lista de ministros celebrantes (tabla ministro_celebrante)
                            if ($ministrosCelebrantes && $ministrosCelebrantes->num_rows > 0) {
                                $ministrosCelebrantes->data_seek(0);
                                while($m = $ministrosCelebrantes->fetch_assoc()){
                                    echo "<option value='{$m['IdMinCel']}'>" . htmlspecialchars($m['Nom'] . ' ' . $m['Ape']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                        <button class="btn btn-outline-secondary" type="button" id="btnNuevoMinistro" title="Agregar Nuevo Ministro">+</button>
                    </div>
                </div>
            </div>

            <!-- Datos Bautizado -->
            <h6 class="text-primary border-bottom pb-1">Datos del Bautizado</h6>
            <div class="row g-2 mb-3">
                <div class="col-md-6"><label class="small fw-bold">Nombre</label><input type="text" name="NomInd" id="editNomInd" class="form-control form-control-sm" required data-was-required="true"></div>
                <div class="col-md-6"><label class="small fw-bold">Apellido</label><input type="text" name="ApeInd" id="editApeInd" class="form-control form-control-sm" required data-was-required="true"></div>
                <div class="col-md-3"><label class="small fw-bold">Fecha Nac.</label><input type="date" name="FecNacInd" id="editFecNacInd" class="form-control form-control-sm" required data-was-required="true"></div>
                <div class="col-md-3"><label class="small fw-bold">Lugar Nac.</label><input type="text" name="LugNacInd" id="editLugNacInd" class="form-control form-control-sm" required data-was-required="true"></div>
                <div class="col-md-3"><label class="small fw-bold">Sexo</label><select name="SexInd" id="editSexInd" class="form-select form-select-sm" required data-was-required="true"><option value="1">Masculino</option><option value="2">Femenino</option></select></div>
                <div class="col-md-3"><label class="small fw-bold">Filiación</label><select name="FilInd" id="editFilInd" class="form-select form-select-sm" required data-was-required="true"><option value="1">Reconocido</option><option value="2">Legítimo</option><option value="3">Natural</option></select></div>
            </div>

            <!-- Padres -->
            <h6 class="text-primary border-bottom pb-1">Padres</h6>
            <div class="row g-2 mb-3">
                <div class="col-md-6"><label class="small fw-bold">Madre (Nom)</label><input type="text" name="NomMad" id="editNomMad" class="form-control form-control-sm" required data-was-required="true"></div>
                <div class="col-md-6"><label class="small fw-bold">Madre (Ape)</label><input type="text" name="ApeMad" id="editApeMad" class="form-control form-control-sm" required data-was-required="true"></div>
                <div class="col-md-6"><label class="small fw-bold">Padre (Nom)</label><input type="text" name="NomPad" id="editNomPad" class="form-control form-control-sm"></div>
                <div class="col-md-6"><label class="small fw-bold">Padre (Ape)</label><input type="text" name="ApePad" id="editApePad" class="form-control form-control-sm"></div>
            </div>

            <!-- Padrinos -->
            <h6 class="text-primary border-bottom pb-1">Padrinos</h6>
            <div class="row g-2 mb-3">
                <div class="col-md-6"><label class="small fw-bold">Padrino (Nom)</label><input type="text" name="Pad1Nom" id="editPad1Nom" class="form-control form-control-sm" required data-was-required="true"></div>
                <div class="col-md-6"><label class="small fw-bold">Padrino (Ape)</label><input type="text" name="Pad1Ape" id="editPad1Ape" class="form-control form-control-sm" required data-was-required="true"></div>
                <div class="col-md-6"><label class="small fw-bold">Madrina (Nom)</label><input type="text" name="Pad2Nom" id="editPad2Nom" class="form-control form-control-sm" required data-was-required="true"></div>
                <div class="col-md-6"><label class="small fw-bold">Madrina (Ape)</label><input type="text" name="Pad2Ape" id="editPad2Ape" class="form-control form-control-sm" required data-was-required="true"></div>
            </div>

            <!-- Otros -->
            <div class="row g-2">
                <div class="col-md-8"><label class="small fw-bold">Observaciones</label><textarea name="NotMar" id="editNotMar" class="form-control form-control-sm"></textarea></div>
                <div class="col-md-4"><label class="small fw-bold">Reg. Civil</label><input type="text" name="RegCiv" id="editRegCiv" class="form-control form-control-sm"></div>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardarEdicion">Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>

<!-- ======================= MODAL NUEVO MINISTRO ======================= -->
<div class="modal fade" id="modalNuevoMinistro" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Registrar Nuevo Ministro</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevoMinistro">
                    <div class="mb-3">
                        <label class="form-label small">Nombre</label>
                        <input type="text" id="newMinNom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Apellido</label>
                        <input type="text" id="newMinApe" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Jerarquía</label>
                        <select id="newMinJer" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php 
                            if ($jerarquias && $jerarquias->num_rows > 0) {
                                $jerarquias->data_seek(0);
                                while($j = $jerarquias->fetch_assoc()){
                                    echo "<option value='{$j['CodJer']}'>" . htmlspecialchars($j['NomJer']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarNuevoMinistro">Guardar</button>
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
    const sexo = $("#filtroSexo").val();
    
    const fecNacIni = $("#filtroFecNacIni").val();
    const fecNacFin = $("#filtroFecNacFin").val();
    
    const fecBautIni = $("#filtroFecBautIni").val();
    const fecBautFin = $("#filtroFecBautFin").val();

    return filas.filter(function () {
      const $tr = $(this);
      
      // 1. Filtro de Texto (Buscador)
      if ($tr.text().toLowerCase().indexOf(search) === -1) return false;

      // 2. Filtro de Sexo
      const rowSexo = $tr.data("sexo");
      if (sexo && rowSexo != sexo) return false;

      // 3. Filtro Fecha Nacimiento (Rango)
      const rowFecNac = $tr.data("fecnac"); // YYYY-MM-DD
      if (fecNacIni && rowFecNac < fecNacIni) return false;
      if (fecNacFin && rowFecNac > fecNacFin) return false;

      // 4. Filtro Fecha Bautizo (Rango)
      const rowFecBaut = $tr.data("fecbaut"); // YYYY-MM-DD
      if (fecBautIni && rowFecBaut < fecBautIni) return false;
      if (fecBautFin && rowFecBaut > fecBautFin) return false;

      return true;
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
  $("#buscarCelebracion, #filtroSexo, #filtroFecNacIni, #filtroFecNacFin, #filtroFecBautIni, #filtroFecBautFin").on("keyup change", function() {
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
          $("#fichaFechaNac").text(d.fec_nac);
          $("#fichaFechaBaut").text(d.fecha_bautizo);
          $("#fichaLugarBaut").text(d.lugar_bautizo || "");
          $("#fichaPadrinos").text(d.padrinos || "");
          $("#fichaMinistro").text(d.ministro || "");
          $("#fichaRegCiv").text(d.registro_civil || "");
          $("#fichaObs").text(d.observaciones || "");
          $("#fichaTipo").text(d.tipo_celebracion || "Bautizo");
          $("#fichaLibro").text(d.num_libro || "");
          $("#fichaFolio").text(d.num_folio || "");

          // 📷 Lógica para la imagen
          const $imgCont = $("#fichaImagenContainer");
          const $digCont = $("#fichaDigitalizador");
          $imgCont.empty();
          $digCont.empty();

          if (d.imagen) {
            $imgCont.html(`<a href="${d.imagen}" target="_blank" class="btn btn-sm btn-success">📷 Ver Imagen Relacionada</a>`);
            if (d.digitalizador) {
                $digCont.text("Digitalizado por: " + d.digitalizador);
            }
          } else {
            $imgCont.html(`<span class="text-muted fst-italic">No tiene imagen relacionada</span>`);
          }

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

  /* =============== Lógica de Edición =============== */
  $(document).on("click", ".btnEditar", function() {
      const idCel = $(this).data("idcel");
      
      // Limpiar formulario
      $("#formEditarBautizo")[0].reset();

      // Cargar datos
      $.post("?controller=sacrej&action=api_obtener_bautizo_edicion", { idCel: idCel }, function(res) {
          if(res.status === 'ok') {
              const d = res.data.main;
              const padres = res.data.padres;
              const padrinos = res.data.padrinos;

              // Llenar campos principales
              $("#editId").val(d.Id);
              $("#editIdCel").val(d.IdCel);
              $("#editIdInd").val(d.IdInd);
              $("#editNumLib").val(d.NumLib);
              $("#editNumFol").val(d.NumFol);
              $("#editFechCel").val(d.FechCel === '0000-00-00' ? '' : d.FechCel);
              $("#editEstCel").val(d.EstCel);
              $("#editLugar").val(d.Lugar);
              $("#editIdMin").val(d.IdMin); // Asegúrate que el value del option coincida con IdMin
              
              $("#editNomInd").val(d.NomInd);
              $("#editApeInd").val(d.ApeInd);
              $("#editFecNacInd").val(d.FecNacInd === '0000-00-00' ? '' : d.FecNacInd);
              $("#editLugNacInd").val(d.LugNacInd);
              $("#editSexInd").val(d.SexInd);
              $("#editFilInd").val(d.FilInd);
              
              $("#editRegCiv").val(d.RegCiv);
              $("#editNotMar").val(d.NotMar);

              // Llenar Padres
              padres.forEach(p => {
                  if(p.Sex == 2) { // Madre
                      $("#editNomMad").val(p.Nom);
                      $("#editApeMad").val(p.Ape);
                  } else if(p.Sex == 1) { // Padre
                      $("#editNomPad").val(p.Nom);
                      $("#editApePad").val(p.Ape);
                  }
              });

              // Llenar Padrinos
              padrinos.forEach(p => {
                  if(p.Sex == 1) { // Padrino
                      $("#editPad1Nom").val(p.Nom);
                      $("#editPad1Ape").val(p.Ape);
                  } else if(p.Sex == 2) { // Madrina
                      $("#editPad2Nom").val(p.Nom);
                      $("#editPad2Ape").val(p.Ape);
                  }
              });

              new bootstrap.Modal(document.getElementById("modalEditar")).show();
          } else {
              Swal.fire("Error", res.msg || "No se pudieron cargar los datos.", "error");
          }
      }, 'json')
      .fail(function(jqXHR, textStatus, errorThrown) {
          console.error("Error AJAX:", textStatus, errorThrown, jqXHR.responseText);
          Swal.fire("Error de Conexión", "Revise la consola (F12) para más detalles.", "error");
      });
  });

  $("#btnGuardarEdicion").click(function() {
      const form = document.getElementById('formEditarBautizo');

      // 🔹 Validación HTML5
      if (!form.checkValidity()) {
          form.reportValidity();
          Swal.fire("Campos incompletos", "Por favor, complete todos los campos obligatorios.", "warning");
          return;
      }

      const data = $(form).serialize();
      
      $.post("?controller=sacrej&action=api_guardar_edicion_bautizo", data, function(res) {
          if(res.status === 'ok') {
              Swal.fire("Éxito", res.msg, "success").then(() => location.reload());
          } else {
              Swal.fire("Error", res.msg, "error");
          }
      }, 'json');
  });

  /* =============== Lógica Nuevo Ministro (Botón +) =============== */
  $("#btnNuevoMinistro").click(function() {
      $("#formNuevoMinistro")[0].reset();
      // Abrimos el modal encima del actual
      new bootstrap.Modal(document.getElementById("modalNuevoMinistro")).show();
  });

  $("#btnGuardarNuevoMinistro").click(function() {
      const nom = $("#newMinNom").val().trim();
      const ape = $("#newMinApe").val().trim();
      const jer = $("#newMinJer").val();

      if(!nom || !ape || !jer) {
          Swal.fire("Atención", "Todos los campos son obligatorios", "warning");
          return;
      }

      $.post("?controller=sacrej&action=agregar_ministro", { Nom: nom, Ape: ape, CodJer: jer }, function(res) {
          if(res.status === 'ok') {
              // Agregar al select y seleccionarlo
              const newOption = new Option(nom + " " + ape, res.id, true, true);
              $("#editIdMin").append(newOption).trigger('change');
              
              // Cerrar modal
              const modalEl = document.getElementById("modalNuevoMinistro");
              const modal = bootstrap.Modal.getInstance(modalEl);
              modal.hide();
              
              Swal.fire("Éxito", "Ministro agregado correctamente", "success");
          } else {
              Swal.fire("Error", res.msg, "error");
          }
      }, 'json');
  });

  // Render inicial
  renderTabla();
});
</script>
