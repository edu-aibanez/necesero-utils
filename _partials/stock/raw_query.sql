SELECT
    l.`product_reference` AS `referencia`,
    l.`product_name` AS `producto`,
    SUM(l.`product_quantity`) AS `cantidad`,
    p.`location` AS `ubicacion`
FROM
    `pr_order_detail` l
    INNER JOIN `pr_product` p ON l.`product_id` = p.`id_product`
    INNER JOIN `pr_orders` o ON l.`id_order` = o.`id_order`
WHERE
    o.`current_state` = 3
    AND l.`id_order` BETWEEN 16566 AND 16615
GROUP BY
    l.`product_reference`, l.`product_name`, p.`location`
ORDER BY
    `cantidad` DESC;
