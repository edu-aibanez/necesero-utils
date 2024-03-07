<?php
$select = "SELECT
    l.`product_reference` AS `referencia`,
    l.`product_name` AS `producto`,
    SUM(l.`product_quantity`) AS `cantidad_vendida`
    FROM
    `pr_order_detail` l
    LEFT JOIN `pr_orders` o ON (l.`id_order` = o.`id_order`)";

// 4: Enviado; 5: Entregado; 21: Listo en tienda
// Antes: AND o.`current_state` IN (4, 5, 21)" >>> Ahora: AND o.`valid` = 1"
$where = " WHERE o.`valid` = 1";

$group_by = " GROUP BY l.`product_id`";

$order_by = " ORDER BY `cantidad_vendida` DESC";
