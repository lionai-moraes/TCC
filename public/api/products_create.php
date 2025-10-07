<?php
require_once '../../db.php';
require_once '../require_login.php';
header('Content-Type: application/json; charset=utf-8');

$id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
$sku = trim($_POST['sku'] ?? '');
$name = trim($_POST['name'] ?? '');
$category = $_POST['category'] ?? null;
$min_qty = (int)($_POST['min_qty'] ?? 0);
$price = (float)($_POST['price'] ?? 0);
$supplier_id = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
$is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

if (!$sku || !$name) { http_response_code(422); echo json_encode(['error'=>'SKU e Nome são obrigatórios']); exit; }

$pdo = db();
try {
  if ($id) {
    $sql = "UPDATE products SET sku=?, name=?, category=?, min_qty=?, price=?, supplier_id=?, is_active=?
            WHERE id=? AND owner_id=?";
    $ok = $pdo->prepare($sql)->execute([$sku,$name,$category,$min_qty,$price,$supplier_id,$is_active,$id,$AUTH_USER_ID]);
    if (!$ok) throw new Exception('Produto não encontrado');
  } else {
    $sql = "INSERT INTO products (owner_id, sku, name, category, min_qty, price, supplier_id, is_active)
            VALUES (?,?,?,?,?,?,?,?)";
    $pdo->prepare($sql)->execute([$AUTH_USER_ID,$sku,$name,$category,$min_qty,$price,$supplier_id,$is_active]);
  }
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['error'=>$e->getMessage()]);
}
