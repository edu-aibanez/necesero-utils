<?php

$select = "SELECT
    SQL_CALC_FOUND_ROWS p.`id_product` AS `id_producto`,
    p.`reference` AS `referencia`,
    p.`ean13` AS `ean13`,
    ROUND(sa.`price`, 2) AS `precio_imp_excl`,
    ROUND(
        sa.`price` + (
            sa.`price` * (
                SELECT
                    (t.`rate` / 100)
                FROM
                    `pr_tax_rule` r
                    LEFT JOIN `pr_tax` t ON r.`id_tax` = t.`id_tax`
                WHERE
                    r.`id_tax_rules_group` = sa.`id_tax_rules_group`
                    AND r.`id_country` = 3
                LIMIT
                    1
            )
        ), 2
    ) AS `precio_imp_incl`,
    pl.`name` AS `producto`,
    sa.`active` AS `estado`
FROM
    `pr_product` p
    LEFT JOIN `pr_product_lang` pl ON (
        pl.`id_product` = p.`id_product`
        AND pl.`id_lang` = 3
        AND pl.`id_shop` = 1
    )
    JOIN `pr_product_shop` sa ON (
        p.`id_product` = sa.`id_product`
        AND sa.id_shop = 1
    )
";

$where = " WHERE 1";

$order_by = " ORDER BY `estado` desc, `id_producto` desc";
