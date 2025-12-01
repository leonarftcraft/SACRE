

<!-- Panel de bienvenida -->
<div class="container" id="welcome-panel">
    <div class="mt-5 pt-5 text-center">
        <h1>¡Bienvenido(a), <?php echo $nombre; ?>!</h1>
        <p>Tu rol es: <strong><?php echo $rolNombre; ?></strong></p>
    </div>

    <!-- Carrusel de frases -->
    <div id="frasesCarousel" class="carousel slide mt-4" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <p>"La felicidad es un viaje, no un destino." - Séneca</p>
            </div>
            <div class="carousel-item">
                <p>"De minúsculas semillas crecen árboles gigantes."</p>
            </div>
            <div class="carousel-item">
                <p>"El sabio comienza por hacer lo que quiere enseñar y después enseña." - Confucio</p>
            </div>
            <div class="carousel-item">
                <p>"Encender una antorcha para iluminar el camino de otros ilumina el nuestro."</p>
            </div>
        </div>
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#frasesCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#frasesCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#frasesCarousel" data-bs-slide-to="2"></button>
            <button type="button" data-bs-target="#frasesCarousel" data-bs-slide-to="3"></button>
        </div>
    </div>
</div>

