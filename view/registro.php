<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SACRA</title>
  <link rel="stylesheet" href="view/css/bootstrap.min.css">
  <link rel="stylesheet" href="view/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100" style="background-color: #f8f7f7;">

    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="?controller=sacrej&action=index">
                <img src="view/images/logo.png" alt="Logo sacrej" style="height: 70px; width: auto; margin-right: 20px;">
                SACRA
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="?controller=sacrej&action=index">INICIO</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="?controller=sacrej&action=registro">REGISTRARSE</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?controller=sacrej&action=iniciar">INICIAR SESIÓN</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card" id="registroCard">
        <div class="card-header text-white">
          <h2 class="mb-0">Registro</h2>
        </div>
        <div class="card-body">
          <form id="registroForm" action="?controller=sacrej&action=registrar_usuario" method="POST" autocomplete="off">

            <!-- ===== Sección 1 ===== -->
            <div class="form-section" id="section1">
              <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="usuario" name="usuario" autocomplete="new-usuario">
                <div id="usuarioError" class="text-danger small mt-1"></div>
              </div>
              <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <div class="input-group">
                  <input type="password" class="form-control" id="contrasena" name="contrasena" autocomplete="new-password">
                  <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <img src="view/images/invisible.png" id="passwordIcon" width="20" height="20">
                  </button>
                </div>
                <div id="contrasenaError" class="text-danger small mt-1"></div>
              </div>
              <div class="mb-3">
                <label for="tipo_usuario" class="form-label">Tipo de Usuario</label>
                <select class="form-select" id="tipo_usuario" name="tipo_usuario" autocomplete="off">
                  <option value="">Seleccione un tipo</option>
                  <!-- Roles en modo SOLICITUD -->
                  <option value="100">Administrador(a) (solicitud)</option>
                  <option value="200">Ministro (solicitud)</option>
                  <option value="300">Secretario(a) (solicitud)</option>
                  <option value="400">Coordinador(a) (solicitud)</option>
                  <option value="500">Catequista (solicitud)</option>
                </select>
                <div id="tipoError" class="text-danger small mt-1"></div>
              </div>
              <button type="button" class="btn btn-primary" id="next1">Siguiente</button>
            </div>

            <!-- ===== Sección 2 ===== -->
            <div class="form-section d-none" id="section2">

              <!-- NUEVO CAMPO: CÉDULA (será el ID del usuario) -->
              <div class="mb-3">
                <label for="cedula" class="form-label">Cédula de identidad</label>
                <input type="text" class="form-control" id="cedula" name="cedula" autocomplete="off">
                <div id="cedulaError" class="text-danger small mt-1"></div>
              </div>

              <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" autocomplete="new-nombre">
                <div id="nombreError" class="text-danger small mt-1"></div>
              </div>
              <div class="mb-3">
                <label for="apellido" class="form-label">Apellido</label>
                <input type="text" class="form-control" id="apellido" name="apellido" autocomplete="new-apellido">
                <div id="apellidoError" class="text-danger small mt-1"></div>
              </div>
              <button type="button" class="btn btn-secondary" id="back2">Atrás</button>
              <button type="button" class="btn btn-primary" id="next2">Siguiente</button>
            </div>

            <!-- ===== Sección 3 ===== -->
            <div class="form-section d-none" id="section3">
              <h5>Preguntas de Seguridad</h5>

              <!-- Contenedor dinámico -->
              <div id="preguntas-container"></div>

              <button type="button" class="btn btn-secondary" id="back3">Atrás</button>
              <button type="submit" class="btn btn-success">Registrar</button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<footer style="background-color: #616a6b; color: #ffffff;" class="py-4">
    <div class="container">
        <div class="row">
            <!-- Contacto -->
            <div class="col-md-4 mb-3">
                <h3>Contacto</h3>
                <address>
                    <p>
                        <img src="view/images/location.png" alt="Ubicación" style="width: 20px; margin-right: 8px;">
                        UBICACION
                    </p>

                    <p>
                        <img src="view/images/phone.png" alt="Teléfono" style="width: 20px; margin-right: 8px;">
                        +00 0000 000-00-00
                    </p>
                    <p>
                        <img src="view/images/mail.png" alt="Correo Electrónico" style="width: 20px; margin-right: 8px;">
                        EJEMPLE@****.COM
                    </p>
                </address>
            </div>

            <!-- Enlaces Rápidos -->
            <div class="col-md-4 mb-3">
                <h3>Enlaces rápidos</h3>
                <ul class="list-unstyled">
                    <li>
                        <a href="https://getbootstrap.com/docs/5.0/getting-started/introduction/" class="text-light" target="_blank">BOOTSTRAP</a>
                   </li>

                    <li>
                        <a href="https://fonts.google.com/icons" class="text-light" target="_blank">ICONOS</a>
                    </li>
                </ul>
            </div>

            <!-- Redes Sociales -->
            <div class="col-md-4 mb-3">
                <h3>Síguenos</h3>
                <div class="social-icons">
                    <a href="https://www.facebook.com" class="me-2" target="_blank"><img src="view/images/facebook.png" alt="Facebook" style="width: 30px;"></a>
                    <a href="https://x.com" class="me-2" target="_blank"><img src="view/images/twitter.png" alt="Twitter" style="width: 30px;"></a>
                    <a href="https://www.instagram.com" class="me-2" target="_blank"><img src="view/images/instagram.png" alt="Instagram" style="width: 30px;"></a>
                </div>
            </div>
        </div>

        <hr class="mt-4 mb-3" style="border-color: #444;">
        <div class="row">
            <div class="col-md-12 text-center">
                <p class="mb-0">&copy; 2025 SACRA. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</footer>

<!-- JS -->
<script src="view/js/jquery-3.6.0.min.js"></script>
<script src="view/js/bootstrap.bundle.min.js"></script>
<script src="view/js/sweetalert.js"></script>
<script>
$(document).ready(function(){
  // === Lista de preguntas ===
  const preguntas = [
    "¿Cuál es el nombre de tu primera mascota?",
    "¿En qué ciudad naciste?",
    "¿Cuál es tu comida favorita?",
    "¿Cuál fue tu primer colegio?",
    "¿Cómo se llama tu mejor amigo de la infancia?",
    "¿Cuál es tu película favorita?",
    "¿Cuál es tu libro favorito?",
    "¿Cuál es tu canción favorita?",
    "¿En qué calle creciste?",
    "¿Cómo se llama tu abuelo materno?",
    "¿Cuál fue tu primer empleo?",
    "¿Cuál es el segundo nombre de tu madre?"
  ];

  // === Generar selects dinámicos ===
  function generarPreguntas(){
    let html = "";
    for(let i=1;i<=4;i++){
      html += `
        <div class="mb-3">
          <label for="pregunta${i}" class="form-label">Pregunta ${i}</label>
          <select class="form-select pregunta" id="pregunta${i}" name="pregunta${i}">
            <option value="">Seleccione una pregunta</option>
            ${preguntas.map(p => `<option value="${p}">${p}</option>`).join("")}
          </select>
          <input type="text" class="form-control mt-2 respuesta" id="respuesta${i}" name="respuesta${i}" placeholder="Respuesta" autocomplete="off">
        </div>
      `;
    }
    $("#preguntas-container").html(html);
  }
  generarPreguntas();

  // === Evitar selección repetida de preguntas ===
  $(document).on("change", ".pregunta", function(){
    let seleccionadas = $(".pregunta").map(function(){ return $(this).val(); }).get();
    $(".pregunta").each(function(){
      let valActual = $(this).val();
      $(this).find("option").each(function(){
        if(seleccionadas.includes($(this).val()) && $(this).val() !== valActual){
          $(this).hide();
        } else {
          $(this).show();
        }
      });
    });
  });

  // === Mostrar/Ocultar contraseña ===
  $('#togglePassword').on('click', function() {
    const passwordInput = $('#contrasena');
    const icon = $('#passwordIcon');
    if (passwordInput.attr('type') === 'password') {
      passwordInput.attr('type', 'text');
      icon.attr('src', 'view/images/visible.png');
    } else {
      passwordInput.attr('type', 'password');
      icon.attr('src', 'view/images/invisible.png');
    }
  });

  // === Funciones de validación ===
  function validarUsuario(u){
    let errores = [];
    if (!u) errores.push("El usuario es obligatorio");
    if (!/^[A-Za-z0-9]+$/.test(u)) errores.push("Solo se permiten letras y números");
    if (!/[A-Z]/.test(u)) errores.push("Debe tener al menos una mayúscula");
    if (!/[a-z]/.test(u)) errores.push("Debe tener al menos una minúscula");
    if (!/[0-9]/.test(u)) errores.push("Debe tener al menos un número");
    return errores;
  }

  function validarContrasena(c){
    let errores = [];
    if (!c) errores.push("La contraseña es obligatoria");
    if (c.length < 8 || c.length > 16) errores.push("Debe tener entre 8 y 16 caracteres");
    if (!/[A-Z]/.test(c)) errores.push("Debe contener una mayúscula");
    if (!/[a-z]/.test(c)) errores.push("Debe contener una minúscula");
    if (!/[0-9]/.test(c)) errores.push("Debe contener un número");
    if (!/[!@#$%^&*(),.?\":{}|<>]/.test(c)) errores.push("Debe contener un carácter especial");
    return errores;
  }

  function validarNombre(n){
    return /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/.test(n);
  }

  // NUEVA: validación de cédula (solo números, largo razonable)
  function validarCedula(c){
    if (!c) return "La cédula es obligatoria";
    if (!/^[0-9]{6,10}$/.test(c)) return "La cédula debe tener entre 6 y 10 dígitos numéricos";
    return "";
  }

  // === Validación en blur ===
  $("#usuario").on("blur", function(){
    let errores = validarUsuario($(this).val().trim());
    if(errores.length > 0){
      $("#usuarioError").html(errores.join("<br>"));
      $(this).addClass("is-invalid").removeClass("is-valid");
    } else {
      $("#usuarioError").text("");
      $(this).removeClass("is-invalid").addClass("is-valid");
    }
  });

  $("#contrasena").on("blur", function(){
    let errores = validarContrasena($(this).val().trim());
    if(errores.length > 0){
      $("#contrasenaError").html(errores.join("<br>"));
      $(this).addClass("is-invalid").removeClass("is-valid");
    } else {
      $("#contrasenaError").text("");
      $(this).removeClass("is-invalid").addClass("is-valid");
    }
  });

  $("#tipo_usuario").on("blur change", function(){
    if(!$(this).val()){
      $("#tipoError").text("Debe seleccionar un tipo de usuario");
      $(this).addClass("is-invalid").removeClass("is-valid");
    } else {
      $("#tipoError").text("");
      $(this).removeClass("is-invalid").addClass("is-valid");
    }
  });

  $("#nombre").on("blur", function(){
    if(!validarNombre($(this).val().trim())){
      $("#nombreError").text("Nombre inválido (solo letras, 2-50 caracteres)");
      $(this).addClass("is-invalid").removeClass("is-valid");
    } else {
      $("#nombreError").text("");
      $(this).removeClass("is-invalid").addClass("is-valid");
    }
  });

  $("#apellido").on("blur", function(){
    if(!validarNombre($(this).val().trim())){
      $("#apellidoError").text("Apellido inválido (solo letras, 2-50 caracteres)");
      $(this).addClass("is-invalid").removeClass("is-valid");
    } else {
      $("#apellidoError").text("");
      $(this).removeClass("is-invalid").addClass("is-valid");
    }
  });

  $("#cedula").on("blur", function(){
    const error = validarCedula($(this).val().trim());
    if(error){
      $("#cedulaError").text(error);
      $(this).addClass("is-invalid").removeClass("is-valid");
    } else {
      $("#cedulaError").text("");
      $(this).removeClass("is-invalid").addClass("is-valid");
    }
  });

  // === Navegación entre secciones ===
  $("#next1").click(function(){
    if($("#usuarioError").text() || $("#contrasenaError").text() || $("#tipoError").text()){
      return; // Evita avanzar si hay errores
    }
    $("#section1").addClass("d-none");
    $("#section2").removeClass("d-none");
  });

  $("#next2").click(function(){
    if($("#cedulaError").text() || $("#nombreError").text() || $("#apellidoError").text()){
      return; // Evita avanzar si hay errores
    }
    $("#section2").addClass("d-none");
    $("#section3").removeClass("d-none");
  });

  $("#back2").click(function(){
    $("#section2").addClass("d-none");
    $("#section1").removeClass("d-none");
  });

  $("#back3").click(function(){
    $("#section3").addClass("d-none");
    $("#section2").removeClass("d-none");
  });

  // === Validación final antes de enviar ===
  $("#registroForm").on("submit", function(e){
    e.preventDefault();

    // Validar selects y respuestas
    let preguntasSeleccionadas = [];
    let respuestas = [];
    for (let i=1; i<=4; i++){
      let p = $("#pregunta"+i).val();
      let r = $("#respuesta"+i).val().trim();
      if (!p || !r){
        Swal.fire("Error", "Debe completar todas las preguntas y respuestas", "error");
        return;
      }
      if (preguntasSeleccionadas.includes(p)){
        Swal.fire("Error", "Las preguntas deben ser diferentes", "error");
        return;
      }
      if (respuestas.includes(r)){
        Swal.fire("Error", "Las respuestas deben ser diferentes", "error");
        return;
      }
      preguntasSeleccionadas.push(p);
      respuestas.push(r);
    }

    // 🔹 Construir datos para enviar
    let datos = {
      usuario: $("#usuario").val().trim(),
      contrasena: $("#contrasena").val().trim(),
      tipo_usuario: $("#tipo_usuario").val(),
      cedula: $("#cedula").val().trim(),        // NUEVO CAMPO
      nombre: $("#nombre").val().trim(),
      apellido: $("#apellido").val().trim(),
      pregunta1: $("#pregunta1").val(),
      respuesta1: $("#respuesta1").val().trim(),
      pregunta2: $("#pregunta2").val(),
      respuesta2: $("#respuesta2").val().trim(),
      pregunta3: $("#pregunta3").val(),
      respuesta3: $("#respuesta3").val().trim(),
      pregunta4: $("#pregunta4").val(),
      respuesta4: $("#respuesta4").val().trim()
    };

    // 🔹 Enviar al backend
    $.post("?controller=sacrej&action=registrar_usuario", datos, function(res){
      if(res.error){
        Swal.fire("Error", res.error, "error");
      } else if(res.success){
        Swal.fire("Éxito", res.success, "success").then(()=>{
          window.location.href = "?controller=sacrej&action=iniciar";
        });
      }
    }, "json").fail(function(){
      Swal.fire("Error", "No se pudo conectar con el servidor", "error");
    });
  });

});
</script>
</body>
</html>
