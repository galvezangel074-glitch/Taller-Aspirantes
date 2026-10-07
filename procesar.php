<?php
/* =====================================================================
   procesar.php
   BACKEND del formulario. Se encarga de:
     1. Validar que los campos no estén vacíos.
     2. Sanear y estandarizar los textos (ej. "norma" -> "Norma").
     3. Calcular la edad y verificar el rango de 18 a 70 años.
     4. Validar las extensiones de imagen permitidas.
     5. Guardar la foto de forma segura en ./uploaded_files/.
   ===================================================================== */

/* ---------------------------------------------------------------------
   PASO 0: SEGURIDAD — solo se acepta el método POST.
   Si alguien entra escribiendo procesar.php en la barra de direcciones,
   lo devolvemos al formulario.
   --------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$errores = [];   // Acumula todos los mensajes de error encontrados
$fotoGuardada = '';   // Nombre final del archivo guardado

/* ---------------------------------------------------------------------
   PASO 0.1: Si la foto excede el post_max_size de PHP, el servidor
   descarta TODO el envío y $_POST llega vacío. Sin este aviso el usuario
   vería "todos los campos están vacíos" sin entender por qué.
   --------------------------------------------------------------------- */
if (empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $errores[] = 'El envío superó el tamaño máximo que acepta el servidor. '
               . 'Suba una fotografía más liviana.';
}

/* ---------------------------------------------------------------------
   FUNCIÓN DE APOYO: Formato Tipo Título
   La técnica pedida es ucwords(strtolower($texto)): primero todo a
   minúsculas para evitar inconsistencias y luego la primera letra de
   cada palabra en mayúscula.

   OJO: ucwords() y strtolower() trabajan byte a byte y NO entienden
   UTF-8, por lo que rompen las vocales acentuadas y la ñ del español:
       ucwords(strtolower("JOSÉ"))  ->  "JosÉ"   (incorrecto)
       ucwords(strtolower("NÚÑEZ")) ->  "NÚÑez"  (incorrecto)
   Por eso usamos su equivalente multibyte, que da el resultado correcto:
       mb_convert_case(mb_strtolower(...)) -> "José", "Núñez"
   Si la extensión mbstring no estuviera activa, se usa ucwords(strtolower()).
   --------------------------------------------------------------------- */
function formatoTipoTitulo($texto)
{
    // Limpieza de espacios: colapsa los espacios repetidos del interior
    // ("sofia    isabel" -> "sofia isabel"), además del trim() ya aplicado.
    $texto = preg_replace('/\s+/u', ' ', $texto);

    if (function_exists('mb_convert_case')) {
        return mb_convert_case(mb_strtolower($texto, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
    return ucwords(strtolower($texto));
}

/* ---------------------------------------------------------------------
   PASO 1: SANEAMIENTO Y NORMALIZACIÓN
   - strip_tags()        -> elimina etiquetas HTML/PHP maliciosas.
   - trim()              -> quita espacios al inicio y al final.
   - formatoTipoTitulo() -> ucwords(strtolower()) seguro con acentos
                            ("sofia" -> "Sofia", "josé" -> "José").
   - strtoupper()        -> Mayúsculas cerradas (para identificaciones).
   --------------------------------------------------------------------- */
$nombre         = formatoTipoTitulo(trim(strip_tags($_POST['nombre'] ?? '')));
$apellido       = formatoTipoTitulo(trim(strip_tags($_POST['apellido'] ?? '')));
$identificacion = strtoupper(preg_replace('/\s+/u', ' ', trim(strip_tags($_POST['identificacion'] ?? ''))));
$fechaNac       = trim(strip_tags($_POST['fecha_nacimiento'] ?? ''));
$sexo           = trim(strip_tags($_POST['sexo'] ?? ''));

/* ---------------------------------------------------------------------
   PASO 2: VALIDACIÓN DE CAMPOS VACÍOS (estructura IF)
   --------------------------------------------------------------------- */
if ($nombre === '') {
    $errores[] = 'El campo Nombre es obligatorio.';
}
if ($apellido === '') {
    $errores[] = 'El campo Apellido es obligatorio.';
}
if ($identificacion === '') {
    $errores[] = 'El campo Identificación es obligatorio.';
}
if ($fechaNac === '') {
    $errores[] = 'El campo Fecha de Nacimiento es obligatorio.';
}

// El sexo se valida contra una lista blanca: nunca se confía en el valor recibido.
if (!in_array($sexo, ['Hombre', 'Mujer'], true)) {
    $errores[] = 'Debe seleccionar una opción válida en el campo Sexo.';
}

/* ---------------------------------------------------------------------
   PASO 3: CÁLCULO DE LA EDAD Y VALIDACIÓN DEL RANGO 18 - 70 AÑOS
   --------------------------------------------------------------------- */
$edad = null;

if ($fechaNac !== '') {
    $nacimiento = DateTime::createFromFormat('Y-m-d', $fechaNac);
    $hoy = new DateTime();

    if (!$nacimiento || $nacimiento->format('Y-m-d') !== $fechaNac) {
        $errores[] = 'La Fecha de Nacimiento no tiene un formato válido.';
    } elseif ($nacimiento > $hoy) {
        $errores[] = 'La Fecha de Nacimiento no puede ser una fecha futura.';
    } else {
        // diff()->y devuelve los años completos transcurridos
        $edad = $nacimiento->diff($hoy)->y;

        if ($edad < 18 || $edad > 70) {
            $errores[] = 'La edad del aspirante debe estar entre 18 y 70 años. '
                       . 'Edad calculada: ' . $edad . ' años.';
        }
    }
}

/* ---------------------------------------------------------------------
   PASO 4: VALIDACIÓN DE LA FOTOGRAFÍA
   --------------------------------------------------------------------- */
$extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
$tamanoMaximo   = 2 * 1024 * 1024;        // 2 MB
$carpetaDestino = './uploaded_files/';
$extension      = '';

if (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
    $errores[] = 'Debe adjuntar la Fotografía del Aspirante.';

} elseif ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    // Mensaje específico según el código devuelto por PHP
    switch ($_FILES['foto']['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $errores[] = 'La fotografía supera el tamaño máximo permitido (2 MB).';
            break;
        case UPLOAD_ERR_PARTIAL:
            $errores[] = 'La fotografía se subió de forma incompleta. Intente nuevamente.';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
        case UPLOAD_ERR_CANT_WRITE:
            $errores[] = 'El servidor no pudo escribir el archivo temporal. Contacte al administrador.';
            break;
        default:
            $errores[] = 'Ocurrió un error al subir la fotografía (código: '
                       . (int) $_FILES['foto']['error'] . ').';
    }

} else {
    // basename() evita ataques de ruta del tipo ../../archivo.php
    $nombreOriginal = basename($_FILES['foto']['name']);
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

    // 4.1 Extensión permitida
    if (!in_array($extension, $extensionesPermitidas, true)) {
        $errores[] = 'Formato de imagen no permitido. Solo se aceptan: '
                   . implode(', ', $extensionesPermitidas) . '.';
    }

    // 4.2 Tamaño máximo
    if ($_FILES['foto']['size'] > $tamanoMaximo) {
        $errores[] = 'La fotografía supera el tamaño máximo permitido de 2 MB.';
    }

    // 4.3 Verificar que el archivo sea REALMENTE una imagen (no un .php renombrado)
    $infoImagen = @getimagesize($_FILES['foto']['tmp_name']);
    if ($infoImagen === false) {
        $errores[] = 'El archivo enviado no es una imagen válida.';
    }
}

/* ---------------------------------------------------------------------
   PASO 5: SI TODO ES CORRECTO, SE GUARDA LA FOTO DE FORMA SEGURA
   Se genera un nombre nuevo: nunca se confía en el nombre original.
   --------------------------------------------------------------------- */
if (empty($errores)) {

    // Si la carpeta no existe se crea, y SIEMPRE se verifica que tenga su
    // .htaccess. Sin esta comprobación, una carpeta recreada quedaría
    // abierta al navegador y se perdería la protección exigida.
    if (!is_dir($carpetaDestino)) {
        mkdir($carpetaDestino, 0755, true);
    }
    if (!is_file($carpetaDestino . '.htaccess')) {
        file_put_contents(
            $carpetaDestino . '.htaccess',
            "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n"
            . "<IfModule !mod_authz_core.c>\n    Order allow,deny\n    Deny from all\n</IfModule>\n"
            . "Options -Indexes -ExecCGI\n"
        );
    }

    // El nombre se genera desde cero: identificación + fecha/hora + 4 caracteres
    // aleatorios, para que dos subidas en el mismo segundo no se sobrescriban.
    $idLimpio     = preg_replace('/[^A-Za-z0-9]/', '', $identificacion);
    $fotoGuardada = 'aspirante_' . $idLimpio . '_' . date('Ymd_His')
                  . '_' . bin2hex(random_bytes(2)) . '.' . $extension;
    $rutaFinal    = $carpetaDestino . $fotoGuardada;

    // move_uploaded_file() garantiza que el archivo provenga de una subida HTTP
    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $rutaFinal)) {
        $errores[] = 'No se pudo guardar la fotografía en el servidor. '
                   . 'Verifique los permisos de la carpeta uploaded_files/.';
    } else {
        @chmod($rutaFinal, 0644);
    }
}

/* ---------------------------------------------------------------------
   PASO 6: SALIDA EN PANTALLA
   htmlspecialchars() en TODA salida para prevenir XSS.
   --------------------------------------------------------------------- */

// INCLUDE 1: Navegación
include 'includes/header.php';
?>

<main class="flex-grow-1 py-5">
    <section class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">

                <?php if (!empty($errores)): ?>

                    <!-- ========== CASO 1: HUBO ERRORES ========== -->
                    <div class="card border-danger shadow-sm">
                        <div class="card-header bg-danger text-white fw-bold">
                            Registro rechazado
                        </div>
                        <div class="card-body">
                            <p class="mb-2">Se encontraron los siguientes problemas:</p>
                            <ul class="mb-0">
                                <?php foreach ($errores as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="index.php" class="btn btn-secondary">Volver al formulario</a>
                        </div>
                    </div>

                <?php else: ?>

                    <!-- ========== CASO 2: REGISTRO EXITOSO ========== -->
                    <div class="card border-success shadow-sm">
                        <div class="card-header bg-success text-white fw-bold">
                            Aspirante registrado correctamente
                        </div>
                        <div class="card-body">
                            <div class="row g-4">

                                <div class="col-12 col-sm-4 text-center">
                                    <?php
                                    /* La carpeta uploaded_files/ está bloqueada al navegador
                                       por el .htaccess, así que la vista previa se arma en
                                       memoria con base64 desde el propio PHP. */
                                    $rutaFoto = $carpetaDestino . $fotoGuardada;
                                    if (is_file($rutaFoto)) {
                                        $mime   = $infoImagen['mime'] ?? 'image/jpeg';
                                        $base64 = base64_encode(file_get_contents($rutaFoto));
                                        echo '<img src="data:' . htmlspecialchars($mime) . ';base64,' . $base64 . '" '
                                           . 'class="img-fluid rounded border" alt="Fotografía del aspirante">';
                                    }
                                    ?>
                                </div>

                                <div class="col-12 col-sm-8">
                                    <table class="table table-sm align-middle mb-0 w-100"
                                           style="table-layout: fixed;">
                                        <tbody>
                                            <tr>
                                                <th scope="row" class="w-50">Nombre:</th>
                                                <td><?php echo htmlspecialchars($nombre); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Apellido:</th>
                                                <td><?php echo htmlspecialchars($apellido); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Identificación:</th>
                                                <td><?php echo htmlspecialchars($identificacion); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Fecha de Nacimiento:</th>
                                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($fechaNac))); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Edad:</th>
                                                <td><?php echo htmlspecialchars((string) $edad); ?> años</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Sexo:</th>
                                                <td><?php echo htmlspecialchars($sexo); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Archivo guardado:</th>
                                                <td class="text-break">
                                                    <code class="small text-break"><?php echo htmlspecialchars($fotoGuardada); ?></code>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="index.php" class="btn btn-primary">Registrar otro aspirante</a>
                        </div>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </section>
</main>

<?php
// INCLUDE 2: Footer
include 'includes/footer.php';
?>
