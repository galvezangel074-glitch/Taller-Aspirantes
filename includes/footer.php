<?php
/* =====================================================================
   includes/footer.php
   Módulo de PIE DE PÁGINA común: eslogan, enlaces rápidos, redes y
   copyright con año dinámico generado por PHP.
   ===================================================================== */

// SEGURIDAD: módulo de include. No debe abrirse directamente en el navegador.
if (basename($_SERVER['PHP_SELF']) === 'footer.php') {
    header('Location: ../index.php');
    exit;
}
?>

<!-- ============ ETIQUETA SEMÁNTICA <footer> ============ -->
<footer class="bg-dark text-white text-center py-4 mt-auto">
    <div class="container">

        <!-- Eslogan o identificación institucional -->
        <p class="mb-1 fw-semibold">
            Portal de Gestión de Aspirantes &mdash; Lic. en Ciberseguridad, Universidad Tecnológica de Panamá
        </p>

        <!-- Enlaces rápidos y de contacto -->
        <div class="mb-2">
            <a href="index.php" class="text-white text-decoration-none mx-2 small">Inicio</a> |
            <a href="#" class="text-white text-decoration-none mx-2 small">Políticas de Privacidad</a> |
            <a href="#" class="text-white text-decoration-none mx-2 small">Términos de Uso</a>
        </div>

        <!-- Redes sociales y contacto (Bootstrap Icons) -->
        <div class="mb-2">
            <a href="angel.galvez" class="text-white mx-2" aria-label="GitHub"><i class="bi bi-github fs-5"></i></a>
            <a href="angel.galvez" class="text-white mx-2" aria-label="LinkedIn"><i class="bi bi-linkedin fs-5"></i></a>
            <a href="mailto:angel.galvesoporte@utp.ac.pa" class="text-white mx-2" aria-label="Correo de soporte">
                <i class="bi bi-envelope-fill fs-5"></i>
            </a>
        </div>

        <!-- Copyright con año dinámico en PHP -->
        <p class="text-white-50 small mb-0">
            &copy; <?php echo date('Y'); ?> Universidad Tecnológica de Panamá. Todos los derechos reservados.
        </p>

    </div>
</footer>

<!-- Bootstrap JS (necesario para el menú colapsable del navbar) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- Cierre de las etiquetas HTML abiertas en el header.php -->
</body>
</html>
