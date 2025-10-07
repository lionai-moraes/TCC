<?php
require_once '../../db.php';
require_once '../require_login.php';

$q = $_GET['q'] ?? null;
$sql = "SELECT p.*,
        COALESCE(s.stock_qty,0) AS stock_qty
        FROM products p
        LEFT JOIN (
          SELECT product_id,
                 SUM(CASE WHEN type='IN' THEN qty
                          WHEN type='OUT' THEN -qty
                          WHEN type='ADJUST' THEN qty ELSE 0 END) AS stock_qty
          FROM stock_movements
          WHERE owner_id=?
          GROUP BY product_id
        ) s ON s.product_id = p.id
        WHERE p.is_active=1 AND p.owner_id=?";
$params = [$AUTH_USER_ID, $AUTH_USER_ID];

if ($q) {
  $sql .= " AND (p.name LIKE ? OR p.sku LIKE ? OR p.category LIKE ?)";
  $like = "%$q%";
  $params = array_merge($params, [$like,$like,$like]);
}

$sql .= " ORDER BY p.name ASC LIMIT 500";
$stmt = db()->prepare($sql);
$stmt->execute($params);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($stmt->fetchAll());
