<?php
// Incluir el archivo de configuración
require_once('.env.php');

// Icnluir el archivo de inicialización
require_once('_partials/init.php');


if ($_SESSION['role_id'] > '4') {
  // Redirigir a una página de acceso no autorizado o mostrar un mensaje de error
  header("Location: unauthorized.php");
  exit();
}

// Datos de la conexión a la base de datos
$servidor = DB_HOST;
$usuario = DB_USERNAME;
$contrasena = DB_PASSWORD;
$basedatos = DB_DATABASE;

// Conexión a la base de datos
$conexion = new mysqli($servidor, $usuario, $contrasena, $basedatos);

// Verificar la conexión
if ($conexion->connect_error) {
  die("Error en la conexión: " . $conexion->connect_error);
}

// Consulta SQL
require_once('_partials/bestselling/query.php');

// Variables para el filtrado
isset($_POST['fecha_desde']) ? $_SESSION['fecha_desde'] = strval($_POST['fecha_desde']) : '';
isset($_POST['fecha_hasta']) ? $_SESSION['fecha_hasta'] = strval($_POST['fecha_hasta']) : '';

$fecha_desde = isset($_SESSION['fecha_desde']) ? $_SESSION['fecha_desde'] : '';
$fecha_hasta = isset($_SESSION['fecha_hasta']) ? $_SESSION['fecha_hasta'] : '';

$filtros = [];

// Modificar la consulta para incluir el filtrado por fechas
if (!empty($fecha_desde) && !empty($fecha_hasta)) {
  $filtros[] = " o.`invoice_date` BETWEEN '$fecha_desde' AND DATE_ADD('$fecha_hasta', INTERVAL 1 DAY)";
  // La fecha_hasta tendrá un día más para que la fecha final sea inclusive
} else {
/* $fecha_desde = '2023-01-01';
$fecha_hasta = "'" . date('Y-m-d') . "'"; */
$filtros[] = " o.`invoice_date` BETWEEN '2023-01-01' AND CURRENT_DATE()";
}

// Si los filtros no están vacíos, agregarlos a la consulta
if (!empty($filtros)) {
  $consulta = $select . $where . ' AND ' . implode(' AND ', $filtros) . $group_by . $order_by;
} else {
  $consulta = $select . $where . $group_by . $order_by;
}

// Paginación

// Comprueba si se ha enviado el formulario de selección de resultados por página
if (isset($_POST['resultados_por_pagina'])) {
  $_SESSION['resultados_por_pagina'] = intval($_POST['resultados_por_pagina']);
}

$resultados_por_pagina = isset($_SESSION['resultados_por_pagina']) ? $_SESSION['resultados_por_pagina'] : 25;

$pagina_actual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
$inicio = ($pagina_actual - 1) * $resultados_por_pagina;

$total_registros = mysqli_num_rows($conexion->query($consulta));
$total_paginas = ceil($total_registros / $resultados_por_pagina);

// Modifica la consulta para aplicar la paginación
$consulta_paginada = $consulta . " LIMIT $inicio, $resultados_por_pagina";

// Exportación a CSV

// Elimina el archivo CSV si existe
unlink('_partials/bestselling/temp.csv');

// Abre un archivo temporal para escribir los datos CSV
$archivo_csv = fopen('_partials/bestselling/temp.csv', 'w');

// Genera un nombre de archivo aleatorio
$randomFilename = mt_rand(1000000000, 9999999999);

// Ejecutar la consulta
$csv_query = $conexion->query($consulta);

if ($csv_query->num_rows > 0) {

  // Agregar la fila de encabezado al archivo CSV
  fputcsv($archivo_csv, ['Referencia', 'Producto', 'Cantidad vendida']);

  while ($fila = $csv_query->fetch_assoc()) {
    // Escribe en el archivo CSV
    fputcsv($archivo_csv, $fila);
  }

  // Cierra el archivo CSV
  fclose($archivo_csv);
}
?>

<!DOCTYPE html>
<html lang="es">

<?php
  // Definir el título de la página
  $title_page = "Productos más vendidos";
  require_once('_partials/head.php');
?>

<body>
  <?php include_once '_partials/header.php'; ?>
  <div class="container">
    <h1>Productos más vendidos</h1>

    <?php
    if ($csv_query->num_rows > 0) : ?>

      <div id="csv-download">
        <a href='_partials/bestselling/temp.csv' download='<?= $randomFilename?>.csv'>
          <i class="fa-solid fa-file-export"></i>
          Exportar
        </a>
      </div>

    <?php endif;

    // Ejecutar la consulta paginada

    // COMPROBACIÓN EXTRA
    $double_check = mysqli_query($conexion, $consulta_paginada);
    if (!$double_check) {
      die('Error en la consulta: ' . mysqli_error($conexion));
    }

    $resultado = $conexion->query($consulta_paginada);
    ?>

    <!-- Comienza la tabla HTML -->
    <div class="list-container">
      <table id="list">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Referencia</th>
            <th>Producto</th>
            <th>Cantidad vendida</th>
            <th>Acciones</th>
          </tr>
          <tr>

            <?php include_once '_partials/bestselling/filters.php'; ?>

          </tr>
        </thead>
        <tbody>

          <?php
          if ($resultado->num_rows > 0): ?>
            <?php
              // Itera a través de los resultados y muestra cada fila en la tabla
              while ($fila = $resultado->fetch_assoc()) {
            ?>
                <tr>
                  <td><!-- Fecha --></td>
                  <td class="text-center"><?php echo $fila['referencia']; ?></td>
                  <td><?php echo $fila['producto']; ?></td>
                  <td class="text-left"><?php echo $fila['cantidad_vendida']; ?></td>
                  <td><!-- Acciones --></td>
                </tr>
            <?php
              }
          else: ?>
              <tr>
                <td colspan="9" class="text-center">No se encontraron resultados.</td>
              </tr>
          <?php
          endif; ?>

        </tbody>
      </table>
      <?php
        // Paginación
        $total_registros > 0 ? include_once '_partials/bestselling/pagination.php': '';
      ?>
    </div>

    <?php
    // Cierra la conexión a la base de datos
    $conexion->close();
    ?>

  </div>
</body>

</html>
