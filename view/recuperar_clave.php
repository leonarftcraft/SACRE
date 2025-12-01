<?php
require_once "controller/sacrej.controller.php";
$controller = new SacrejController();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SACRA</title>
    <link rel="stylesheet" href="view/css/bootstrap.min.css">
    <link rel="stylesheet" href="view/css/style.css">
</head>
<body style="background-color: #f8f7f7;">
        <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="?controller=sacrej&action=index">  <img src="view/images/logo.png" alt="Logo SACREJ" style="height: 70px; width: auto; margin-right: 20px;">SACRA</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="?controller=sacrej&action=index">INICIO</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?controller=sacrej&action=registro">REGISTRARSE</a>
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
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-white">
                        <h2 class="mb-0">Recuperar Contraseña</h2>
                    </div>
                    <div class="card-body">

                        <!-- Paso 1: Verificación de usuario -->
                        <div id="step1">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="usuario" placeholder="Ingrese su usuario">
                                <div id="usuarioError" class="text-danger mt-1"></div>
                            </div>
                            <button class="btn btn-primary w-100" id="verificarUsuario">Siguiente</button>
                        </div>

                        <!-- Paso 2: Preguntas de seguridad -->
                        <div id="step2" style="display:none;">
                            <form id="preguntasForm">
                                <div id="preguntasContainer"></div>
                                <button class="btn btn-primary w-100 mt-3" id="validarPreguntas">Siguiente</button>
                            </form>
                        </div>

                        <!-- Paso 3: Nueva contraseña -->
                        <div id="step3" style="display:none;">
                            <form id="nuevaClaveForm">
                                <div class="mb-3">
                                    <label for="nuevaClave" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="nuevaClave" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmClave" class="form-label">Confirmar Contraseña</label>
                                    <input type="password" class="form-control" id="confirmClave" required>
                                </div>
                                <button class="btn btn-success w-100" id="actualizarClave">Actualizar Contraseña</button>
                            </form>
                        </div>

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
                    <p class="mb-0">&copy; 2025 MVC. Todos los derechos reservados.</p>
                </div>
            </div>
        
        </div>
    </footer>

<script src="view/js/jquery-3.6.0.min.js"></script>
<script src="view/js/bootstrap.bundle.min.js"></script>
<script src="view/js/sweetalert.js"></script>
<script>
$(document).ready(function(){

    let usuarioActual = "";
    let preguntasRandom = [];

    // Paso 1: Verificar usuario
    $('#verificarUsuario').on('click', function(){
        let usuario = $('#usuario').val().trim();
        if(usuario === "") {
            $('#usuarioError').text("Ingrese su usuario");
            return;
        }
        $.ajax({
            url: '?controller=sacrej&action=recuperar_usuario',
            type: 'POST',
            data: {usuario: usuario},
            dataType: 'json',
            success: function(res){
                if(res.success){
                    usuarioActual = usuario;
                    preguntasRandom = res.preguntas;
                    // Mostrar preguntas
                    let html = '';
                    preguntasRandom.forEach((p, index)=>{
                        html += `<div class="mb-3">
                                    <label class="form-label">${p.pregunta}</label>
                                    <input type="text" class="form-control respuesta" data-id="${p.id}" required>
                                 </div>`;
                    });
                    $('#preguntasContainer').html(html);
                    $('#step1').hide();
                    $('#step2').show();
                } else {
                    Swal.fire('Error', res.error, 'error');
                }
            },
            error: function(){
                Swal.fire('Error', 'Ocurrió un error al verificar el usuario', 'error');
            }
        });
    });

    // Paso 2: Validar respuestas
    $('#validarPreguntas').on('click', function(e){
        e.preventDefault();
        let respuestas = [];
        $('.respuesta').each(function(){
            respuestas.push({id: $(this).data('id'), respuesta: $(this).val().trim()});
        });

        $.ajax({
            url: '?controller=sacrej&action=validar_preguntas',
            type: 'POST',
            data: {usuario: usuarioActual, respuestas: respuestas},
            dataType: 'json',
            success: function(res){
                if(res.success){
                    $('#step2').hide();
                    $('#step3').show();
                } else {
                    Swal.fire('Error', res.error, 'error');
                }
            },
            error: function(){
                Swal.fire('Error', 'Ocurrió un error al validar respuestas', 'error');
            }
        });
    });

    // Paso 3: Actualizar contraseña
    $('#actualizarClave').on('click', function(e){
        e.preventDefault();
        let clave1 = $('#nuevaClave').val();
        let clave2 = $('#confirmClave').val();

        if(clave1 === "" || clave2 === ""){
            Swal.fire('Error','Debe completar ambos campos','error');
            return;
        }
        if(clave1 !== clave2){
            Swal.fire('Error','Las contraseñas no coinciden','error');
            return;
        }

        $.ajax({
            url: '?controller=sacrej&action=actualizar_clave',
            type: 'POST',
            data: {usuario: usuarioActual, nuevaClave: clave1},
            dataType: 'json',
            success: function(res){
                if(res.success){
                    Swal.fire('Éxito','Contraseña actualizada correctamente','success').then(()=>{
                        window.location.href = '?controller=sacrej&action=iniciar';
                    });
                } else {
                    Swal.fire('Error', res.error, 'error');
                }
            },
            error: function(){
                Swal.fire('Error', 'Ocurrió un error al actualizar la contraseña', 'error');
            }
        });
    });

});
</script>

</body>
</html>
