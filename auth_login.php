<?php
require_once 'db.php';
session_start();

$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

if (!$email || !$pass) { http_response_code(422); echo 'Dados obrigatórios'; exit; }

$pdo = db();
$stmt = $pdo->prepare("SELECT id, name, email, password_hash FROM users WHERE email=? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($pass, $user['password_hash'])) {
  http_response_code(401); echo 'Credenciais inválidas'; exit;
}

$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];

header('Location: public/index.php');
