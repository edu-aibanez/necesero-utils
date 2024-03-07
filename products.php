<?php
// Incluir el archivo de configuración
require_once('.env.php');

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
require_once('_partials/products/query.php');

// Variables para el filtrado

// Recogemos los valores enviados por el formulario y los almacenamos en variables de sesión
isset($_POST['referencia']) ? $_SESSION['referencia'] = strval($_POST['referencia']) : '';
isset($_POST['producto']) ? $_SESSION['producto'] = strval($_POST['producto']) : '';
isset($_POST['estado']) ? $_SESSION['estado'] = strval($_POST['estado']) : '';

// Almacenamos los valores de las variables de sesión
$referencia = isset($_SESSION['referencia']) ? $_SESSION['referencia'] : '';
$producto = isset($_SESSION['producto']) ? $_SESSION['producto'] : '';
$estado = isset($_SESSION['estado']) ? $_SESSION['estado'] : '';


$filtros = [];

// Modificar la consulta para incluir el filtrado por referencia
if (!empty($referencia)) {
  $filtros[] = " p.`reference` LIKE '%$referencia%'";
  // $filtros[] = !empty($referencia) ? " AND l.`product_reference` LIKE '%$referencia%'" : '';
}

// Modificar la consulta para incluir el filtrado por producto
if (!empty($producto)) {
  $filtros[] = " pl.`name` LIKE '%$producto%'";
}

// Modificar la consulta para incluir el filtrado por estado
if (isset($estado) && $estado != '') {
  $filtros[] = " sa.`active` = $estado";
}

if (!empty($filtros)) {
  // $consulta = $select . $where . $order_by;
  $consulta = $select . $where . ' AND ' . implode(' AND ', $filtros) . $order_by;
} else {
  $consulta = $select . $where . $order_by;
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
unlink('_partials/products/temp.csv');

// Abre un archivo temporal para escribir los datos CSV
$archivo_csv = fopen('_partials/products/temp.csv', 'w');

// Genera un nombre de archivo aleatorio
$randomFilename = mt_rand(1000000000, 9999999999);

// Ejecutar la consulta
$csv_query = $conexion->query($consulta);

if ($csv_query->num_rows > 0) {

  //Definir el encabezado del archivo CSV
  $cabecera_csv = [
    'ID producto', 'Referencia', 'EAN13', 'Precio (imp. excl.)', 'Precio (imp. incl.)',
    'Nombre', 'Activo', 'ID imagen', 'Categoría', 'Cantidad'
  ];

  // Agregar la fila de encabezado al archivo CSV
  fputcsv($archivo_csv, $cabecera_csv);

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
  $title_page = "Catálogo de productos";
  require_once('_partials/head.php');
?>

<body>
  <?php include_once '_partials/header.php'; ?>
  <div class="container">
    <h1>Catálogo de productos</h1>

    <?php
    if ($csv_query->num_rows > 0) : ?>

      <div id="csv-download">
        <a href='_partials/products/temp.csv' download='<?= $randomFilename?>.csv'>
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
            <th>Referencia</th>
            <th>Producto</th>
            <th>PVP</th>
            <th>EAN-13</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
          <tr>

            <?php include_once '_partials/products/filters.php'; ?>

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
                  <td class="text-right"><?php echo $fila['referencia']; ?></td>
                  <td><?php echo $fila['producto']; ?></td>
                  <td class="text-right"><?php echo $fila['precio_imp_incl']; ?>€</td>
                  <td class="text-right"><?php echo $fila['ean13']; ?></td>
                  <td class="text-right">
                    <?php
                      $fila['estado'] ? print '<i class="fa-solid fa-check"></i>' : print '<i class="fa-solid fa-times"></i>';
                    ?>
                  </td>
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
        $total_registros > 0 ? include_once '_partials/products/pagination.php': '';
      ?>
    </div>

    <?php
    // Cierra la conexión a la base de datos
    $conexion->close();
    ?>

  </div>
</body>

</html>
