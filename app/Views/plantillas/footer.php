</main>

<!-- Footer -->
<footer class="container-fluid bg-dark text-white mt-4" id="footer">
    <div class="row p-4">
        <!-- Información -->
        <div class="col-md-4">
            <h4>Información</h4>
            <a class="nav-link text-white" href="<?= base_url('/nosotros'); ?>">Términos y Usos</a>
        </div>

        <!-- Medios de pago (texto genérico, sin imágenes) -->
        <div class="col-md-4">
            <h4>Medios de pago</h4>
            <p>Tarjetas de crédito y débito</p>
            <p>Transferencias bancarias</p>
            <p>Pago en efectivo</p>
        </div>

        <!-- Contacto -->
        <div class="col-md-4">
            <h4>Contacto</h4>
            <p>Dirección: Junín 1648, Corrientes Capital</p>
            <p>Teléfono: +123456789</p>
            <p><strong>Email:</strong> 
                <a href="mailto:info@info@arenaApp" target="_blank" class="text-white">
                    info@arenaApp
                </a>
            </p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS local -->
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js'); ?>"></script>

<!-- Bootstrap JS desde CDN (comentado, activar si querés usarlo) -->
<!--
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
        crossorigin="anonymous"></script>
-->

</body>
</html>
