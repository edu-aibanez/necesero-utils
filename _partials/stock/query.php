<?php

$select = " SELECT
l.`product_reference` AS `referencia`,
l.`product_name` AS `producto`,
SUM(l.`product_quantity`) AS `cantidad`,
p.`location` AS `ubicacion`
FROM
`pr_order_detail` l
INNER JOIN `pr_product` p ON l.`product_id` = p.`id_product`
INNER JOIN `pr_orders` o ON l.`id_order` = o.`id_order`";

/* 2: Pago aceptado; 3: Preparación en curso */
$where = " WHERE o.`current_state` IN (2) ";

$group_by = " GROUP BY
l.`product_reference`,l.`product_name`, p.`location`";

$order_by = " ORDER BY
`cantidad` DESC";
