<?php
session_start();
require 'config/db.php';

$email = $_POST['email'] ?? '';

$stmt = $pdo->prepare("SELECT id_cliente, nome FROM clientes WHERE email = ?");
$stmt->execute([$email]);

if ($cliente = $stmt->fetch()) {
    $_SESSION['cliente_id'] = $cliente['id_cliente'];
    $_SESSION['cliente_nome'] = $cliente['nome'];
    header('Location: cliente/painel.php');
} else {
    header('Location: index.php?erro=1');
}
?>