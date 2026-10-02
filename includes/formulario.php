<?php
/* =====================================================================
   includes/formulario.php
   Módulo del FORMULARIO de registro de aspirantes.
   Se incluye dentro de <main><section> en index.php.

   IMPORTANTE: enctype="multipart/form-data" es obligatorio para que el
   archivo de imagen pueda viajar al servidor.
   ===================================================================== */

// SEGURIDAD: módulo de include. No debe abrirse directamente en el navegador.
if (basename($_SERVER['PHP_SELF']) === 'formulario.php') {
    header('Location: ../index.php');
    exit;
}
?>

<div class="card shadow-sm border-0">
    <div class="card-body p-4 p-md-5">

        <form action="procesar.php" method="POST" enctype="multipart/form-data" novalidate>

            <!-- Límite de tamaño sugerido al navegador: 2 MB -->
            <input type="hidden" name="MAX_FILE_SIZE" value="2097152">

            <!-- ---------- Nombre ---------- -->
            <div class="mb-3">
                <label for="nombre" class="form-label fw-bold">Nombre (Requerido):</label>
                <input type="text" class="form-control" id="nombre" name="nombre"
                       placeholder="Ej. Sofía Isabel" maxlength="50" required>
            </div>

            <!-- ---------- Apellido ---------- -->
            <div class="mb-3">
                <label for="apellido" class="form-label fw-bold">Apellido (Requerido):</label>
                <input type="text" class="form-control" id="apellido" name="apellido"
                       placeholder="Ej. Gálvez Rodríguez" maxlength="50" required>
            </div>

            <!-- ---------- Identificación ---------- -->
            <div class="mb-3">
                <label for="identificacion" class="form-label fw-bold">Identificación (Requerido):</label>
                <input type="text" class="form-control" id="identificacion" name="identificacion"
                       placeholder="Ej. 8-123-4567 o PE-45-678" maxlength="20" required>
            </div>

            <!-- ---------- Fecha de Nacimiento ---------- -->
            <div class="mb-3">
                <label for="fecha_nacimiento" class="form-label fw-bold">Fecha de Nacimiento (Requerido):</label>
                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                       placeholder="dd/mm/aaaa" required>
                <div class="form-text">La edad debe estar entre 18 y 70 años.</div>
            </div>

            <!-- ---------- Sexo ---------- -->
            <div class="mb-4">
                <label class="form-label fw-bold d-block">Sexo (Requerido):</label>
                <div class="btn-group w-100" role="group" aria-label="Selección de sexo">
                    <input type="radio" class="btn-check" name="sexo" id="sexoHombre" value="Hombre" required>
                    <label class="btn btn-outline-secondary" for="sexoHombre">Hombre</label>

                    <input type="radio" class="btn-check" name="sexo" id="sexoMujer" value="Mujer" required>
                    <label class="btn btn-outline-secondary" for="sexoMujer">Mujer</label>
                </div>
            </div>

            <!-- ---------- Fotografía ---------- -->
            <div class="mb-4">
                <label for="foto" class="form-label fw-bold">
                    Fotografía del Aspirante (png, jpg, jpeg, gif, webp):
                </label>
                <input type="file" class="form-control" id="foto" name="foto"
                       accept=".png,.jpg,.jpeg,.gif,.webp" required>
                <div class="form-text">Tamaño máximo permitido: 2 MB.</div>
            </div>

            <!-- ---------- Botón de envío ---------- -->
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg w-100">Registrar Aspirante</button>
            </div>

        </form>

    </div>
</div>
