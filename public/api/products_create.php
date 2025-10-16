<?php
require_once '../../db.php';
require_once '../require_login.php';
header('Content-Type: application/json; charset=utf-8');

$id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
$sku = trim($_POST['sku'] ?? '');
$name = trim($_POST['name'] ?? '');
$category = $_POST['category'] ?? null;
$unit = trim($_POST['unit'] ?? '');
$last_purchase_at = $_POST['last_purchase_at'] ?? null;
$last_sale_at = $_POST['last_sale_at'] ?? null;
$custo_compra = (float)($_POST['custo_compra'] ?? 0);
$numero_prateleira = trim($_POST['numero_prateleira'] ?? '');
$initial_qty = max(0, (int)($_POST['initial_qty'] ?? 0));
$min_qty = (int)($_POST['min_qty'] ?? 0);
$price = (float)($_POST['price'] ?? 0);
$supplier_id = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
$is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

if (!$sku || !$name) { http_response_code(422); echo json_encode(['error'=>'SKU e Nome são obrigatórios']); exit; }

$pdo = db();
try {
  $pdo->beginTransaction();
  if ($id) {
    $sql = "UPDATE products SET sku=?, name=?, category=?, unit=?, last_purchase_at=?, last_sale_at=?, custo_compra=?, numero_prateleira=?, min_qty=?, price=?, supplier_id=?, is_active=?
            WHERE id=? AND owner_id=?";
    $ok = $pdo->prepare($sql)->execute([$sku,$name,$category,$unit,$last_purchase_at,$last_sale_at,$custo_compra,$numero_prateleira,$min_qty,$price,$supplier_id,$is_active,$id,$AUTH_USER_ID]);
    if (!$ok) throw new Exception('Produto não encontrado');
  } else {
    $sql = "INSERT INTO products (owner_id, sku, name, category, unit, last_purchase_at, last_sale_at, custo_compra, numero_prateleira, min_qty, price, supplier_id, is_active)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$AUTH_USER_ID,$sku,$name,$category,$unit,$last_purchase_at,$last_sale_at,$custo_compra,$numero_prateleira,$min_qty,$price,$supplier_id,$is_active]);
    $productId = (int)$pdo->lastInsertId();
    if ($initial_qty > 0) {
      $ins = $pdo->prepare("INSERT INTO stock_movements (owner_id, product_id, type, qty, reason, ref_code, created_by) VALUES (?,?,?,?,?,?,?)");
      $ins->execute([$AUTH_USER_ID, $productId, 'IN', $initial_qty, 'Estoque inicial', 'INIT', $AUTH_USER_ID]);
    }
  }
  $pdo->commit();
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) { $pdo->rollBack(); }
  http_response_code(400);
  echo json_encode(['error'=>$e->getMessage()]);
}
