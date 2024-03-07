<?php
require_once('_partials/init.php');

// Verificar si el usuario tiene el rol de administrador
if ($_SESSION['role_id'] > '3') {
  // Redirigir a una página de acceso no autorizado o mostrar un mensaje de error
  header("Location: unauthorized.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<?php
$old_frame_src = "https://lookerstudio.google.com/embed/reporting/df52f206-a41d-46bf-aa59-09eaf6eabd88/page/p_24huq9ig9c";
$frame_src = "https://lookerstudio.google.com/embed/reporting/74b4afd6-091d-4034-90d2-4a7046a455ea/page/p_h2djwwig9c";

$title_page = "Estadísticas";
require_once('_partials/head.php');
?>

<body style="overflow:hidden;">
  <?php include_once '_partials/header.php';?>

  <div class="stats-container">
    <iframe id="stats" width="100%" height="100%"
      src= "<?= $frame_src; ?>"
      scrolling="no" frameborder="0" style="border:0" allowfullscreen>
    </iframe>
  </div>
</body>
</html>
