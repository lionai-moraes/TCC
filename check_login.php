<?php
session_start();

// Verifica se o usuário está logado
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userName = $isLoggedIn ? ($_SESSION['user_name'] ?? 'Usuário') : '';

// Retorna JSON com o status
header('Content-Type: application/json');
echo json_encode([
    'loggedIn' => $isLoggedIn,
    'userName' => $userName
]);
?>
