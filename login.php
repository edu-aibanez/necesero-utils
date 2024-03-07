<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Conexión con la base de datos SQLite
    $databaseFile = 'sellout.db'; // Ruta al archivo de la base de datos SQLite

    try {
        $db = new PDO("sqlite:$databaseFile");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Error al conectar con la base de datos: " . $e->getMessage());
    }

    function showErrorMessages($errorMessage)
    {
        $output = '<script>';
        $output .= 'document.addEventListener("DOMContentLoaded", function() {';
        $output .= '  var errorDiv = document.getElementById("error-messages");';
        $output .= '  errorDiv.innerHTML = "<p>' . $errorMessage . '</p>";';
        $output .= '});';
        $output .= '</script>';

        return $output;
    }

    // 2. Recepción y validación de los datos recibidos
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if (!empty($email) && !empty($password)) {
            // Encriptar la contraseña
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // 3. Comprobar la existencia del usuario en la base de datos
            $login_query = "SELECT * FROM users WHERE email = :email";
            $stmt = $db->prepare($login_query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if (password_verify($password, $user['password'])) {
                    // Contraseña válida

                    // Establecer variables de sesión
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['email'] = $email;
                    $_SESSION['loggedin'] = true;

                    // Obtener el rol del usuario
                    $role_query = "SELECT role_id FROM user_roles WHERE user_id = :use_id";
                    $stmt = $db->prepare($role_query);
                    $stmt->bindParam(':use_id', $user['id']);
                    $stmt->execute();

                    $role = $stmt->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['role_id'] = $role['role_id'];

                    // Establecer tiempo de expiración de la sesión a 60 minutos
                    $_SESSION['expire'] = time() + (60 * 60);

                    // Redirigir a la página "index.php"
                    header("Location: index.php");
                    exit();
                } else {
                    // Contraseña no válida
                    echo showErrorMessages("Nombre de usuario o contraseña no válidos.");
                }
            } else {
                // Usuario no encontrado
                echo showErrorMessages("Nombre de usuario o contraseña no válidos.");
            }
        } else {
            // Valores vacíos
            echo showErrorMessages("Por favor, ingresa el nombre de usuario y la contraseña.");
        }
    } else {
        // Datos no recibidos
        echo showErrorMessages("Por favor, ingresa el nombre de usuario y la contraseña.");
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<?php
$title_page = 'Login';
require_once('_partials/head.php');
?>

<body>
    <div class="card-container">
        <div class="card">
            <div class="logo">
                <img src="assets/img/logo.png" alt="">
            </div>
            <form action="login.php" method="post">
                <label for="email">
                    <span>Nombre de usuario:</span>
                    <input type="text" id="email" name="email" required>
                </label>

                <label for="password">
                    <span>Contraseña:</span>
                    <input type="password" id="password" name="password" required>
                </label>

                <!-- Div para mostrar mensajes de error -->
                <div id="error-messages"></div>

                <button type="submit">
                    <i class="fa fa-shield" aria-hidden="true"></i>
                    Iniciar sesión
                </button>
            </form>
        </div>
    </div>
</body>

</html>
