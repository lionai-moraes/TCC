<?php
require_once '../../db.php';
require_once '../require_login.php';
header('Content-Type: application/json; charset=utf-8');

$id = (int)($_POST['id'] ?? 0);
if (!$id) { http_response_code(422); echo json_encode(['error'=>'ID inválido']); exit; }

try {
  $sql = "UPDATE products SET is_active=0 WHERE id=? AND owner_id=?";
  $ok = db()->prepare($sql)->execute([$id,$AUTH_USER_ID]);
  if (!$ok) throw new Exception('Produto não encontrado');
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['error'=>$e->getMessage()]);
}
