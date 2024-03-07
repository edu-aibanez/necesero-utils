<?php
$actual_page = basename($_SERVER['SCRIPT_NAME']); // Devuelve el nombre del archivo actual

$enlaces = [
    /* 'stats.php' => ['Estadísticas', 'fa-regular fa-bar-chart', 3], */
    /* 'sellout.php' => ['Sellout', 'fa-regular fa-clock', 2], */
    'list.php' => ['Precios', 'fa-solid fa-table', 4],
    'products.php' => ['Productos', 'fa-solid fa-cubes', 5],
    'stock.php' => ['Stock', 'fa-solid fa-boxes', 5],
    'bestselling.php' => ['Más vendidos', 'fa-solid fa-star', 4],
];

?>
<header>
    <div class="image-container">
        <img src="assets/img/logo.png" alt="" width="188" height="32">
    </div>

    <ul>
        <?php
            foreach ($enlaces as $enlace => [$nombre, $icono, $role_id]) {
                // TODO: Mostrar enlaces según el rol del usuario
                if ($_SESSION['role_id'] <= $role_id) :
                ?>
                <li>
                    <a href="<?php echo $enlace; ?>"
                        class="<?php if($actual_page == $enlace) { echo 'active'; } ?>">
                        <i class="<?php echo $icono; ?>" aria-hidden="true"></i>
                        <?= $nombre; ?>
                    </a>
                </li>
            <?php
                else :
                    continue;
                endif;
            }
        ?>
        <li style="float:right">
            <form action="" method="post" class="logout-button">
                <button type="submit" name="logout">
                    <i class="fa-solid fa-power-off" aria-hidden="true"></i>
                    Cerrar sesión
                </button>
            </form>
        </li>
        <li style="float:right">
            <!-- Muestra el "name" del usuario actual -->
                <button class="logged-user">
                    <i class="fa-solid fa-user" aria-hidden="true"></i>
                    <?= $_SESSION['user_name']; ?>
                </button>
        </li>
    </ul>
</header>
