<?php
require_once('_partials/init.php');

if ($_SESSION['role_id'] > '2') {
  // Redirigir a una página de acceso no autorizado o mostrar un mensaje de error
  header("Location: unauthorized.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<?php
$title_page = "Cuadrar pedidos";
require_once('_partials/head.php'); ?>

<body>
  <?php include_once '_partials/header.php'; ?>

  <div class="container">
    <h1>Cuadrar pedidos</h1>

    <div class="card-container">
      <div class="card">
        <form action="_partials/sellout/sellout.php" method="post" enctype="multipart/form-data">
          <label for="laboratorio">
            <span>Laboratorio:</span>
            <input type="text" id="laboratorio" name="laboratorio" required>
          </label>

          <label for="fechaInicio">
            <span>Fecha inicio:</span>
            <input type="date" id="fechaInicio" name="fechaInicio" required>
          </label>

          <label for="fechaFin">
            <span>Fecha fin:</span>
            <input type="date" id="fechaFin" name="fechaFin" required>
          </label>

          <label for="idInicio">
            <span>ID inicio:</span>
            <input type="number" id="idInicio" name="idInicio" required>
          </label>

          <label for="idFin">
            <span>ID fin:</span>
            <input type="number" id="idFin" name="idFin" required>
          </label>

          <label for="ventas">
            <span>Fichero ventas:</span>
            <input type="file" id="ventas" name="ventas" accept=".xlsx" required>
          </label>

          <label for="pdf">
            <input type="checkbox" name="pdf" id="pdf"><span>Obtener una copia en PDF</span>
          </label>

          <button type="submit">
            <i class="fa-solid fa-check"></i>
            CUADRAR PEDIDOS
          </button>
        </form>
      </div>
    </div>

  </div>

</body>

</html>
