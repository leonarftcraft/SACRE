<?php
// Carpeta donde están las imágenes del carrusel
$carpetaCarrusel = 'view/images/carrusel';

// Tomar todas las imágenes JPG de la carpeta
$imagenesCarrusel = glob($carpetaCarrusel . '/*.jpg');

// Si se encontraron imágenes, mezclarlas aleatoriamente
if ($imagenesCarrusel && count($imagenesCarrusel) > 0) {
    shuffle($imagenesCarrusel); // Cambia el orden cada vez que cargas la página
} else {
    $imagenesCarrusel = [];
}
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

<body class="d-flex flex-column min-vh-100" style="background-color: #f8f7f7;">
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


    <main class="my-4">

        <!-- Mensaje de bienvenida -->
        <h1 id="welcome-title">Bienvenido a la Parroquia Sagrado Corazón de Jesús</h1>
        <p id="welcome-subtitle">Sistema Administrativo de Celebraciones y Registros – SACRA</p>

        <div class="container">
            <?php if (!empty($imagenesCarrusel)) : ?>
                <div id="inicioCarousel" class="carousel slide" data-bs-ride="carousel">
                    
                    <div class="carousel-inner">
                        <?php foreach ($imagenesCarrusel as $index => $rutaImagen) : ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <img 
                                    src="<?= htmlspecialchars($rutaImagen) ?>" 
                                    alt="Imagen carrusel <?= $index + 1 ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Controles -->
                    <button class="carousel-control-prev" type="button" data-bs-target="#inicioCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>

                    <button class="carousel-control-next" type="button" data-bs-target="#inicioCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                        <span class="visually-hidden">Siguiente</span>
                    </button>
                </div>
            <?php else : ?>
                <div class="text-center py-5">
                    <p class="text-muted">No hay imágenes disponibles para el carrusel.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>



   
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
                            <a href="" class="text-light" target="_blank">BOOTSTRAP</a>
                    </li>

                        <li>
                            <a href="" class="text-light" target="_blank">ICONOS</a>
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


    <script src="view/js/jquery-3.6.0.min.js"></script>
    <script src="view/js/bootstrap.bundle.min.js"></script>
    <script src="view/js/sweetalert.js"></script>
   
</body>

</html>