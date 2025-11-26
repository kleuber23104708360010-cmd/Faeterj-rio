<?php
require '../config/db.php';

if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];

    $pdo->prepare("DELETE FROM lojas WHERE id_loja = ?")->execute([$id]);
    header('Location: lojas.php?sucesso=excluido');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nome = $_POST['nome'];
    $endereco = $_POST['endereco'];
    $cidade = $_POST['cidade'];
    $estado = strtoupper($_POST['estado']);
    $tipo_loja = $_POST['tipo_loja'];

    if ($id) {
        $stmt = $pdo->prepare("
            UPDATE lojas
            SET nome = ?, endereco = ?, cidade = ?, estado = ?, tipo_loja = ?
            WHERE id_loja = ?
        ");
        $stmt->execute([$nome, $endereco, $cidade, $estado, $tipo_loja, $id]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO lojas (nome, endereco, cidade, estado, tipo_loja)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nome, $endereco, $cidade, $estado, $tipo_loja]);
    }

    header('Location: lojas.php?sucesso=salvo');
    exit;
}
?>