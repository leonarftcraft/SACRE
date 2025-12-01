<div class="container mt-4">
  <h4 class="text-center mb-4">Consulta de Usuarios del Sistema</h4>

  <?php
  // 🔹 Helper para mostrar nombre del rol
  function nombreRol($rol) {
      $rol = (int)$rol;
      switch ($rol) {
          case 10:
          case 100: return "Administrador(a)";
          case 20:
          case 200: return "Ministro";
          case 30:
          case 300: return "Secretario(a)";
          case 40:
          case 400: return "Coordinador(a)";
          case 50:
          case 500: return "Catequista";
          default:  return "Desconocido";
      }
  }

  $datos = $this->model->obtener_todos("usuarios");
  ?>

  <!-- 🔎 Buscador + Filtro -->
  <div class="row mb-3">
    <div class="col-md-6 mb-2">
      <input type="text" id="buscarUsuario" class="form-control" placeholder="Buscar usuario...">
    </div>
    <div class="col-md-3 mb-2">
      <select id="filtroEstado" class="form-select">
        <option value="todos">Todos los estados</option>
        <option value="activo">Solo activos</option>
        <option value="inactivo">Solo inactivos</option>
      </select>
    </div>
  </div>

  <!-- 📋 Tabla -->
  <table class="table table-striped table-bordered align-middle" id="tablaUsuarios">
    <thead class="table-dark text-center">
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Apellido</th>
        <th>Usuario</th>
        <th>Rol</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($fila = $datos->fetch_assoc()):
          $rol = (int)$fila['RolUsu'];
          $esActivo = $rol < 100; // ✅ 2 dígitos = activo, 3 dígitos = inactivo
          $estadoTexto = $esActivo ? "Activo" : "Inactivo";
          $textoBotonEstado = $esActivo ? "Desactivar" : "Activar";
          $claseBotonEstado = $esActivo ? "btn-warning" : "btn-success";
      ?>
        <tr data-id="<?= $fila['IdUsu']; ?>" data-estado="<?= $estadoTexto; ?>">
          <td class="text-center"><?= $fila['IdUsu']; ?></td>
          <td><?= htmlspecialchars($fila['NomUsu']); ?></td>
          <td><?= htmlspecialchars($fila['ApeUsu']); ?></td>
          <td><?= htmlspecialchars($fila['Usuario']); ?></td>
          <td class="text-center rol-texto"><?= nombreRol($rol); ?></td>
          <td class="text-center estado-texto"><?= $estadoTexto; ?></td>
          <td class="text-center">
            <!-- Activar / Desactivar -->
            <button 
              class="btn btn-sm <?= $claseBotonEstado; ?> btnEstado" 
              data-id="<?= $fila['IdUsu']; ?>">
              <?= $textoBotonEstado; ?>
            </button>

            <!-- Eliminar (solo si inactivo y sin registros) -->
            <button 
              class="btn btn-sm btn-danger btnEliminar" 
              data-id="<?= $fila['IdUsu']; ?>"
              <?= $esActivo ? 'disabled title="Debe desactivar primero el usuario"' : ''; ?>>
              Eliminar
            </button>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
$(document).ready(function() {

  /* =============== FILTROS (buscar + estado) =============== */
  function aplicarFiltros() {
    const busqueda = $("#buscarUsuario").val().toLowerCase();
    const filtroEstado = $("#filtroEstado").val(); // todos | activo | inactivo

    $("#tablaUsuarios tbody tr").each(function() {
      const fila = $(this);
      const textoFila = fila.text().toLowerCase();
      const estado = (fila.data("estado") || "").toString().toLowerCase(); // "activo" / "inactivo"

      const coincideBusqueda = textoFila.indexOf(busqueda) > -1;
      let coincideEstado = true;

      if (filtroEstado === "activo") {
        coincideEstado = (estado === "activo");
      } else if (filtroEstado === "inactivo") {
        coincideEstado = (estado === "inactivo");
      }

      fila.toggle(coincideBusqueda && coincideEstado);
    });
  }

  $("#buscarUsuario").on("keyup", aplicarFiltros);
  $("#filtroEstado").on("change", aplicarFiltros);

  /* =============== Activar / Desactivar usuario =============== */
  $(document).on("click", ".btnEstado", function() {
    const boton = $(this);
    const id = boton.data("id");

    $.post("?controller=sacrej&action=cambiar_estado_usuario", { id: id }, function(res) {
      if (res.success) {
        const fila = $('tr[data-id="'+id+'"]');

        fila.find(".rol-texto").text(res.rol_texto);
        fila.find(".estado-texto").text(res.estado_texto);
        fila.attr("data-estado", res.estado_texto); // 👉 importante para el filtro

        // Actualizar botón de estado
        boton
          .text(res.boton_texto)
          .removeClass("btn-success btn-warning")
          .addClass(res.boton_clase);

        // Habilitar / deshabilitar botón eliminar según estado
        const btnEliminar = fila.find(".btnEliminar");
        if (res.estado_texto === "Activo") {
          btnEliminar.prop("disabled", true)
                     .attr("title", "Debe desactivar primero el usuario");
        } else {
          btnEliminar.prop("disabled", false)
                     .attr("title", "");
        }

        Swal.fire("Éxito", res.message, "success");
        aplicarFiltros(); // por si el cambio afecta al filtro actual
      } else {
        Swal.fire("Error", res.error || "No se pudo cambiar el estado", "error");
      }
    }, "json").fail(function() {
      Swal.fire("Error", "Error de conexión con el servidor", "error");
    });
  });

  /* =============== Eliminar usuario =============== */
  $(document).on("click", ".btnEliminar", function() {
    const boton = $(this);
    const id = boton.data("id");

    Swal.fire({
      title: "¿Eliminar usuario?",
      text: "Solo puede eliminar usuarios inactivos y sin registros asociados.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar"
    }).then((result) => {
      if (!result.isConfirmed) return;

      $.post("?controller=sacrej&action=eliminar_usuario", { id: id }, function(res) {
        if (res.success) {
          $('tr[data-id="'+id+'"]').remove();
          Swal.fire("Eliminado", res.message, "success");
        } else {
          Swal.fire("Error", res.error || "No se pudo eliminar el usuario", "error");
        }
      }, "json").fail(function() {
        Swal.fire("Error", "Error de conexión con el servidor", "error");
      });
    });
  });

});
</script>
