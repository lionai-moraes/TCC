<?php
require_once 'db.php';
session_start();

$name  = trim($_POST['name']  ?? '');
$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password']   ?? '';

if (!$name || !$email || !$pass) {
  http_response_code(422); echo 'Dados obrigatórios'; exit;
}

$hash = password_hash($pass, PASSWORD_DEFAULT);

$pdo = db();
try {
  $pdo->prepare("INSERT INTO users (name,email,password_hash) VALUES (?,?,?)")
      ->execute([$name,$email,$hash]);

  // Redireciona para loading com parâmetro de cadastro
  header('Location: loanding.html?action=register&email=' . urlencode($email));
} catch (Throwable $e) {
  http_response_code(400);
  echo 'Erro no cadastro: ' . $e->getMessage();
}
