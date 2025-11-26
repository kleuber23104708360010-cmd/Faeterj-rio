<?php
require '../config/db.php';

// Excluir
if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    // Verificar se está em locação ativa (opcional, mas seguro)
    $pdo->prepare("DELETE FROM veiculos WHERE id_veiculo = ?")->execute([$id]);
    header('Location: veiculos.php?sucesso=excluido');
    exit;
}

// Salvar (inserir ou atualizar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $placa = $_POST['placa'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $ano = (int)$_POST['ano'];
    $cor = $_POST['cor'] ?? null;
    $id_categoria = (int)$_POST['id_categoria'];
    $id_loja_atual = (int)$_POST['id_loja_atual'];
    $status = $_POST['status'];

    if ($id) {
        // Atualizar
        $stmt = $pdo->prepare("
            UPDATE veiculos
            SET placa = ?, marca = ?, modelo = ?, ano = ?, cor = ?,
                id_categoria = ?, id_loja_atual = ?, status = ?
            WHERE id_veiculo = ?
        ");
        $stmt->execute([$placa, $marca, $modelo, $ano, $cor, $id_categoria, $id_loja_atual, $status, $id]);
    } else {
        // Inserir
        $stmt = $pdo->prepare("
            INSERT INTO veiculos (placa, marca, modelo, ano, cor, id_categoria, id_loja_atual, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$placa, $marca, $modelo, $ano, $cor, $id_categoria, $id_loja_atual, $status]);
    }

    header('Location: veiculos.php?sucesso=salvo');
    exit;
}
?>