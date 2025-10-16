<?php
session_start();

// Verificar se é uma requisição AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

session_unset();
session_destroy();

if ($isAjax) {
    // Retornar JSON para requisições AJAX
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Logout realizado com sucesso']);
} else {
    // Redirecionar para requisições normais
    header('Location: index.html');
}
