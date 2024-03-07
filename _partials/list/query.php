<?php

$select = " SELECT
o.`id_order` AS `id_pedido`,
o.`date_add` AS `fecha_pedido`,

l.`product_reference` AS `referencia`,
l.`product_name` AS `producto`,
l.`product_quantity` AS `cantidad`,
CEILING(l.`reduction_percent`) AS `dto_aplicado`,

/* Precios netos */
ROUND(l.`product_price`, 2) AS `precio_neto_dto_excl`,
ROUND(l.`unit_price_tax_excl`, 2) AS `precio_neto_dto_incl`,

/* Precios de venta al publico */
ROUND(
    l.`product_price` + (
        l.`product_price` * (
            SELECT
                (t.`rate` / 100)
            FROM
                `pr_tax_rule` r
                LEFT JOIN `pr_tax` t ON r.`id_tax` = t.`id_tax`
            WHERE
                r.`id_tax_rules_group` = l.`id_tax_rules_group`
                AND r.`id_country` = 3
            LIMIT
                1
        )
    ), 2
) AS `precio_venta_dto_excl`,
ROUND(l.`unit_price_tax_incl`, 2) AS `precio_venta_dto_incl`,

/* Precios Totales */
ROUND(
    (
        (
            l.`product_price` + (
                l.`product_price` * (
                    SELECT
                        (t.`rate` / 100)
                    FROM
                        `pr_tax_rule` r
                        LEFT JOIN `pr_tax` t ON r.`id_tax` = t.`id_tax`
                    WHERE
                        r.`id_tax_rules_group` = l.`id_tax_rules_group`
                        AND r.`id_country` = 3
                    LIMIT
                        1
                )
            )
        ) * l.`product_quantity`
    ), 2
) AS `precio_total_dto_excl`,
ROUND(l.`total_price_tax_incl`, 2) AS `precio_total_dto_incl`
FROM
`pr_order_detail` l
LEFT JOIN `pr_orders` o ON (l.`id_order` = o.`id_order`)";

$where = " WHERE
o.current_state IN (4, 5, 21, 23)";
// AND l.`reduction_percent` > 0

$order_by = " ORDER BY
o.`id_order` DESC";
