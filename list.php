<?php
// Incluir el archivo de configuración
require_once('.env.php');

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
require_once('_partials/list/query.php');

// Variables para el filtrado

// TODO: Controlar por variables de sesión
/* if (isset($_POST['fecha_desde'])) {
  $_SESSION['fecha_desde'] = intval($_POST['fecha_desde']);
} */

// Recogemos los valores enviados por el formulario y los almacenamos en variables de sesión
isset($_POST['fecha_desde']) ? $_SESSION['fecha_desde'] = strval($_POST['fecha_desde']) : '';
isset($_POST['fecha_hasta']) ? $_SESSION['fecha_hasta'] = strval($_POST['fecha_hasta']) : '';
isset($_POST['referencia']) ? $_SESSION['referencia'] = strval($_POST['referencia']) : '';
isset($_POST['producto']) ? $_SESSION['producto'] = strval($_POST['producto']) : '';

// Almacenamos los valores de las variables de sesión
$fecha_desde = isset($_SESSION['fecha_desde']) ? $_SESSION['fecha_desde'] : '';
$fecha_hasta = isset($_SESSION['fecha_hasta']) ? $_SESSION['fecha_hasta'] : '';
$referencia = isset($_SESSION['referencia']) ? $_SESSION['referencia'] : '';
$producto = isset($_SESSION['producto']) ? $_SESSION['producto'] : '';

// "La fecha_hasta" tendrá un día más
//$fecha_hasta = date('Y-m-d', strtotime($fecha_hasta . ' +1 day'));

/* $fecha_desde = isset($_POST['fecha_desde']) ? $_POST['fecha_desde'] : '';
$fecha_hasta = isset($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : '';
$referencia = isset($_POST['referencia']) ? $_POST['referencia'] : '';
$producto = isset($_POST['producto']) ? $_POST['producto'] : ''; */

$filtros = [];

// Modificar la consulta para incluir el filtrado por fechas
if (!empty($fecha_desde) && !empty($fecha_hasta)) {
    $filtros[] = " o.`date_add` BETWEEN '$fecha_desde' AND DATE_ADD('$fecha_hasta', INTERVAL 1 DAY)";
    // La fecha_hasta tendrá un día más para que la fecha final sea inclusive
} else {
  /* $fecha_desde = '2023-01-01';
  $fecha_hasta = "'" . date('Y-m-d') . "'"; */
  $filtros[] = " o.`date_add` BETWEEN '2023-01-01' AND CURRENT_DATE()";
}

// Modificar la consulta para incluir el filtrado por referencia
if (!empty($referencia)) {
  $filtros[] = " l.`product_reference` LIKE '%$referencia%'";
  // $filtros[] = !empty($referencia) ? " AND l.`product_reference` LIKE '%$referencia%'" : '';
}

// Modificar la consulta para incluir el filtrado por producto
if (!empty($producto)) {
  $filtros[] = " l.`product_name` LIKE '%$producto%'";
}

if (!empty($filtros)) {
  // $consulta = $select . $where . $order_by;
  $consulta = $select . $where . ' AND ' . implode(' AND ', $filtros) . $order_by;
}

// Paginación

// Comprueba si se ha enviado el formulario de selección de resultados por página
if (isset($_POST['resultados_por_pagina'])) {
  $_SESSION['resultados_por_pagina'] = intval($_POST['resultados_por_pagina']);
}

$resultados_por_pagina = isset($_SESSION['resultados_por_pagina']) ? $_SESSION['resultados_por_pagina'] : 25;
//$resultados_por_pagina = isset($_POST['resultados']) ? $_POST['resultados'] : 25;

$pagina_actual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
$inicio = ($pagina_actual - 1) * $resultados_por_pagina;

$total_registros = mysqli_num_rows($conexion->query($consulta));
$total_paginas = ceil($total_registros / $resultados_por_pagina);

// Modifica la consulta para aplicar la paginación
$consulta_paginada = $consulta . " LIMIT $inicio, $resultados_por_pagina";

// Elimina el archivo CSV si existe
unlink('_partials/list/temp.csv');

// Abre un archivo temporal para escribir los datos CSV
$archivo_csv = fopen('_partials/list/temp.csv', 'w');

// Genera un nombre de archivo aleatorio
$randomFilename = mt_rand(1000000000, 9999999999);

// Ejecutar la consulta
$csv_query = $conexion->query($consulta);

if ($csv_query->num_rows > 0) {

  //Definir el encabezado del archivo CSV
  $cabecera_csv = [
    'iD pedido', 'Fecha', 'Referencia', 'Producto', 'Cantidad',
    'Descuento aplicado', 'PVP', 'PVP con descuento', 'Total', 'Total con descuento',
  ];

  $cabecera_csv_2 = [
    'iD pedido', 'Fecha', 'Referencia', 'Producto', 'Cantidad',
    'Descuento aplicado', 'Precio neto', 'Precio neto con descuento',
    'PVP', 'PVP con descuento', 'Total', 'Total con descuento'
  ];

  // Agregar la fila de encabezado al archivo CSV
  fputcsv($archivo_csv, $cabecera_csv_2);

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
  $title_page = "Control de precios";
  require_once('_partials/head.php');
?>

<body>
  <?php include_once '_partials/header.php'; ?>
  <div class="container">
    <h1>Control de precios</h1>

    <?php
    if ($csv_query->num_rows > 0) : ?>

      <div id="csv-download">
        <a href='_partials/list/temp.csv' download='<?= $randomFilename?>.csv'>
          <i class="fa-solid fa-file-export"></i>
          Exportar
        </a>
      </div>

    <?php endif;

    // Ejecutar la consulta paginada
    $resultado = $conexion->query($consulta_paginada);
    ?>

    <!-- Comienza la tabla HTML -->
    <div class="list-container">
      <table id="list">
        <thead>
          <tr>
            <!-- <th>ID pedido</th> -->
            <th>Fecha</th>
            <th>Referencia</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Descuento aplicado</th>
            <th>PVP</th>
            <th>PVP con descuento</th>
            <th>Precio neto</th>
            <th>Precio neto con descuento</th>
            <!-- <th>Total</th> -->
            <!-- <th>Total con descuento</th> -->
          </tr>
          <tr>

            <?php include_once '_partials/list/filters.php'; ?>

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
                  <!-- <td class="text-right"><?php echo $fila['id_pedido']; ?></td> -->
                  <td><?php echo $fila['fecha_pedido']; ?></td>
                  <td class="text-right"><?php echo $fila['referencia']; ?></td>
                  <td><?php echo $fila['producto']; ?></td>
                  <td class="text-right"><?php echo $fila['cantidad']; ?></td>
                  <td class="text-right"><?php echo $fila['dto_aplicado']; ?>%</td>
                  <td class="text-right"><?php echo $fila['precio_venta_dto_excl']; ?>€</td>
                  <td class="text-right"><?php echo $fila['precio_venta_dto_incl']; ?>€</td>
                  <td class="text-right"><?php echo $fila['precio_neto_dto_excl']; ?>€</td>
                  <td class="text-right"><?php echo $fila['precio_neto_dto_incl']; ?>€</td>
                  <!-- <td class="text-right"><?php echo $fila['precio_total_dto_excl']; ?>€</td> -->
                  <!-- <td class="text-right"><?php echo $fila['precio_total_dto_incl']; ?>€</td> -->
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
        $total_registros > 0 ? include_once '_partials/list/pagination.php': '';
      ?>
    </div>

    <?php
    // Cierra la conexión a la base de datos
    $conexion->close();
    ?>

  </div>
</body>

</html>
