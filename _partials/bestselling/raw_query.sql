SELECT
    l.`product_reference` AS `referencia`,
    l.`product_name` AS `producto`,
    SUM(l.`product_quantity`) AS `cantidad_vendida`
FROM
     `pr_order_detail` l
    LEFT JOIN `pr_orders` o ON (l.`id_order` = o.`id_order`)
WHERE
    o.`valid` = 1
    o.`current_state` IN (4, 5, 21) -- Enviado, Entregado, Listo en tienda
    AND o.`invoice_date` BETWEEN '2023-01-01' AND CURRENT_DATE()
GROUP BY
    l.`product_id`
ORDER BY
    `cantidad_vendida` DESC
