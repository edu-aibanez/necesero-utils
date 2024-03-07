<?php
require_once('_partials/init.php');

// Redirigir a la página correspondiente según el rol del usuario
if ($_SESSION['role_id'] >= 4) {
    // Si el usuario logeado tiene el username "seller", mostrar página en blanco
    header("Location: products.php");
  } else {
    header("Location: products.php");
}
