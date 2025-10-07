<?php
session_start();
if (empty($_SESSION['user_id'])) {
  header('Location: login.html');
  exit;
}
$AUTH_USER_ID = (int)$_SESSION['user_id'];
$AUTH_USER_NAME = $_SESSION['user_name'] ?? 'Usuário';
?>
