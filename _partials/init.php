<?php
// Incluir el código necesario para iniciar la sesión
session_start();

// Comprobar si no hay una sesión iniciada
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  // Redirigir al archivo "login.php"
  header("Location: login.php");
  exit();
}

// Si se hace clic en el botón de cierre de sesión
if (isset($_POST['logout'])) {
  // Destruir la sesión y redirigir al archivo "login.php"
  session_destroy();
  header("Location: login.php");
  exit();
}
