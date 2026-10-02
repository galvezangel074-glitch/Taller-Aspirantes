<?php
/* =====================================================================
   index.php
   Página principal: muestra el formulario visual de registro.
   Usa include para modularizar la navegación, el formulario y el footer.
   ===================================================================== */

// INCLUDE 1: Navegación (<head>, <header>, navbar y breadcrumb)
include 'includes/header.php';
?>

<!-- ============ ETIQUETA SEMÁNTICA <main> ============ -->
<main class="flex-grow-1 py-5">

    <!-- ============ ETIQUETA SEMÁNTICA <section> ============ -->
    <section class="container" id="formulario">

        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-7">

                <h1 class="h3 text-center fw-bold mb-4">Formulario de Registro de Aspirantes</h1>

                <?php
                // INCLUDE 2: El formulario
                include 'includes/formulario.php';
                ?>

            </div>
        </div>

    </section>
</main>

<?php
// INCLUDE 3: Footer (pie de página + cierre de </body> y </html>)
include 'includes/footer.php';
?>
