<?php
require_once '../../db.php';
require_once '../require_login.php';
header('Content-Type: application/json; charset=utf-8');

$product_id = (int)($_POST['product_id'] ?? 0);
$type = $_POST['type'] ?? '';
$qty = (int)($_POST['qty'] ?? 0);
$reason = $_POST['reason'] ?? null;
$ref_code = $_POST['ref_code'] ?? null;

if (!$product_id || !in_array($type, ['IN','OUT','ADJUST'], true) || $qty <= 0) {
  http_response_code(422);
  echo json_encode(['error'=>'Dados inválidos']);
  exit;
}

$pdo = db();
$pdo->beginTransaction();
try {
  $own = $pdo->prepare("SELECT id FROM products WHERE id=? AND owner_id=?");
  $own->execute([$product_id,$AUTH_USER_ID]);
  if (!$own->fetch()) throw new Exception('Produto não encontrado para este usuário');

  if ($type === 'OUT') {
    $chk = $pdo->prepare("
      SELECT COALESCE(SUM(CASE WHEN type='IN' THEN qty
                               WHEN type='OUT' THEN -qty
                               WHEN type='ADJUST' THEN qty ELSE 0 END),0) AS stock
      FROM stock_movements
      WHERE product_id=? AND owner_id=? FOR UPDATE");
    $chk->execute([$product_id,$AUTH_USER_ID]);
    $row = $chk->fetch();
    if ((int)$row['stock'] < $qty) throw new Exception('Estoque insuficiente');
  }

  $ins = $pdo->prepare("INSERT INTO stock_movements
    (owner_id, product_id, type, qty, reason, ref_code, created_by)
    VALUES (?,?,?,?,?,?,?)");
  $ins->execute([$AUTH_USER_ID, $product_id, $type, $qty, $reason, $ref_code, $AUTH_USER_ID]);

  $pdo->commit();
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  $pdo->rollBack();
  http_response_code(400);
  echo json_encode(['error'=>$e->getMessage()]);
}
