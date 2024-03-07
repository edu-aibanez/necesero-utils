<?php
// Incluir el archivo de configuración
require_once('.env.php');

// Icnluir el archivo de inicialización
require_once('_partials/init.php');

if ($_SESSION['role_id'] > '5') {
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
require_once('_partials/stock/query.php');

// Variables para el filtrado
isset($_POST['id_desde']) ? $_SESSION['id_desde'] = strval($_POST['id_desde']) : '';
isset($_POST['id_hasta']) ? $_SESSION['id_hasta'] = strval($_POST['id_hasta']) : '';
isset($_POST['ubicacion']) ? $_SESSION['ubicacion'] = strval($_POST['ubicacion']) : '';

$id_desde = isset($_SESSION['id_desde']) ? $_SESSION['id_desde'] : '';
$id_hasta = isset($_SESSION['id_hasta']) ? $_SESSION['id_hasta'] : '';
$ubicacion = isset($_SESSION['ubicacion']) ? $_SESSION['ubicacion'] : '';

$filtros = [];

// Modificar la consulta para incluir el filtrado por referencia
// Modificar la consulta para incluir el filtrado por fechas
if (!empty($id_desde) && !empty($id_hasta)) {
  $filtros[] = " l.`id_order` BETWEEN $id_desde AND $id_hasta";
} // TODO: else last order_id and last order_id - 100


// Modificar la consulta para incluir el filtrado por producto
if (!empty($ubicacion)) {
  $filtros[] = " p.`location` LIKE '%$ubicacion%'";
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
unlink('_partials/stock/temp.csv');

// Abre un archivo temporal para escribir los datos CSV
$archivo_csv = fopen('_partials/stock/temp.csv', 'w');

// Genera un nombre de archivo aleatorio
$randomFilename = mt_rand(1000000000, 9999999999);

// Ejecutar la consulta
$csv_query = $conexion->query($consulta);

if ($csv_query->num_rows > 0) {

  // Agregar la fila de encabezado al archivo CSV
  fputcsv($archivo_csv, ['Referencia', 'Producto', 'Cantidad', 'Ubicacion']);

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
  $title_page = "Productos pendientes";
  require_once('_partials/head.php');
?>

<body>
  <?php include_once '_partials/header.php'; ?>
  <div class="container">
    <h1>Productos de pedidos pendientes</h1>

    <?php
    if ($csv_query->num_rows > 0) : ?>

      <div id="csv-download">
        <a href='_partials/stock/temp.csv' download='<?= $randomFilename?>.csv'>
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
            <th>ID's de Pedido</th>
            <th>Referencia</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Ubicación</th>
            <th>Acciones</th>
          </tr>
          <tr>

            <?php include_once '_partials/stock/filters.php'; ?>

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
                  <td></td>
                  <td class="text-right"><?php echo $fila['referencia']; ?></td>
                  <td><?php echo $fila['producto']; ?></td>
                  <td class="text-right"><?php echo $fila['cantidad']; ?></td>
                  <td> <?php echo $fila['ubicacion']; ?></td>
                  <td></td>
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
        $total_registros > 0 ? include_once '_partials/stock/pagination.php': '';
      ?>
    </div>

    <?php
    // Cierra la conexión a la base de datos
    $conexion->close();
    ?>

  </div>
</body>

</html>
