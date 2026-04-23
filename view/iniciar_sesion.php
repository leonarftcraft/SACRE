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
                <img src="view/images/logo.png" alt="Logo SACREJ" style="height: 70px; width: auto; margin-right: 20px;">SACRA
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="?controller=sacrej&action=index">INICIO</a></li>
                    <li class="nav-item"><a class="nav-link" href="?controller=sacrej&action=registro">REGISTRARSE</a></li>
                    <li class="nav-item"><a class="nav-link active" href="?controller=sacrej&action=iniciar">INICIAR SESIÓN</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card" id="loginCard">
                    <div class="card-header text-white">
                        <h2 class="mb-0">Iniciar Sesión</h2>
                    </div>
                    <div class="card-body">
                        <form id="loginForm" action="?controller=sacrej&action=iniciar_sesion" method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" autocomplete="off" required>
                                <div id="usuarioError" class="error-message text-danger"></div>
                            </div>
                            <div class="mb-3">
                                <label for="contrasena" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="contrasena" name="contrasena" required autocomplete="new-password">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <img src="view/images/invisible.png" alt="Mostrar contraseña" id="passwordIcon" width="20" height="20">
                                    </button>
                                </div>
                                <div id="contrasenaError" class="error-message text-danger"></div>
                            </div>
                            <div>
                                <a href="?controller=sacrej&action=recuperar_clave" class="text-primary">¿Olvidaste tu contraseña?</a>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mt-3">Iniciar Sesión</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer style="background-color: #616a6b; color: #ffffff;" class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h3>Contacto</h3>
                    <address>
                        <p><img src="view/images/location.png" alt="Ubicación" style="width: 20px; margin-right: 8px;">UBICACION</p>
                        <p><img src="view/images/phone.png" alt="Teléfono" style="width: 20px; margin-right: 8px;">+00 0000 000-00-00</p>
                        <p><img src="view/images/mail.png" alt="Correo Electrónico" style="width: 20px; margin-right: 8px;">EJEMPLE@****.COM</p>
                    </address>
                </div>
                <div class="col-md-4 mb-3">
                    <h3>Enlaces rápidos</h3>
                    <ul class="list-unstyled">
                        <li><a href="https://getbootstrap.com/docs/5.0/getting-started/introduction/" class="text-light" target="_blank">BOOTSTRAP</a></li>
                        <li><a href="https://fonts.google.com/icons" class="text-light" target="_blank">ICONOS</a></li>
                    </ul>
                </div>
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

    <script src="view/js/jquery-3.6.0.min.js"></script>
    <script src="view/js/bootstrap.bundle.min.js"></script>
    <script src="view/js/sweetalert.js"></script>
    <script>
        $(document).ready(function() {
            $('#loginForm')[0].reset();

            // Mostrar / ocultar contraseña
            $('#togglePassword').on('click', function() {
                const passwordInput = $('#contrasena');
                const icon = $('#passwordIcon');
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.attr('src', 'view/images/visible.png');
                    icon.attr('alt', 'Ocultar contraseña');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.attr('src', 'view/images/invisible.png');
                    icon.attr('alt', 'Mostrar contraseña');
                }
            });

            // Validación simple: solo que no esté vacío
            function validarCampo(campo) {
                return campo.trim() !== "";
            }

            $('#loginForm').on('submit', function(e) {
                e.preventDefault();

                let usuarioValido = validarCampo($('#usuario').val());
                let contrasenaValido = validarCampo($('#contrasena').val());

                if (!usuarioValido || !contrasenaValido) {
                    alert('Por favor, complete todos los campos.');
                    return;
                }

                // Enviar AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        let response = String(res).trim(); // 🧼 Eliminar espacios en blanco
                        if (response === "1") {
                            Swal.fire({
                                title: "Iniciando Sesión",
                                html: "En breves será redirigido",
                                timer: 2000,
                                timerProgressBar: true,
                                didOpen: () => Swal.showLoading()
                            }).then(() => {
                                window.location.href = '?controller=sacrej&action=index';
                            });
                        } else if (response === "0") {
                            Swal.fire({
                                icon: "error",
                                title: "Oops...",
                                text: "Usuario o contraseña incorrecto",
                            });
                        } else {
                            console.log('Respuesta inesperada del servidor:', response);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Error en el envío:', textStatus, errorThrown);
                        alert('Error en el inicio de sesión. Revise la consola.');
                    }
                });
            });
        });
    </script>
</body>

</html>
