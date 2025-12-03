<?php
require '../config/db.php';

if (isset($_GET['sucesso'])) {
    if ($_GET['sucesso'] === 'salvo') {
        $mensagem_sucesso = "Veículo salvo com sucesso!";
    } elseif ($_GET['sucesso'] === 'excluido') {
        $mensagem_sucesso = "Veículo excluído com sucesso!";
    }
}


$stmt_cat = $pdo->query("SELECT id_categoria, nome_categoria FROM categorias_veiculo");
$categorias = $stmt_cat->fetchAll();

$stmt_lojas = $pdo->query("SELECT id_loja, nome, cidade, estado FROM lojas");
$lojas = $stmt_lojas->fetchAll();

$veiculo = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM veiculos WHERE id_veiculo = ?");
    $stmt->execute([$_GET['editar']]);
    $veiculo = $stmt->fetch();
}

$stmt = $pdo->query("
    SELECT v.id_veiculo, v.placa, v.marca, v.modelo, v.ano, v.cor, v.status,
           cat.nome_categoria, l.nome AS loja_nome
    FROM veiculos v
    LEFT JOIN categorias_veiculo cat ON v.id_categoria = cat.id_categoria
    LEFT JOIN lojas l ON v.id_loja_atual = l.id_loja
    ORDER BY v.marca, v.modelo
");
$veiculos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Veículos - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>Veículos</h2>
    <a href="painel.php">← Voltar ao Painel</a>

    <h3><?= $veiculo ? 'Editar Veículo' : 'Adicionar Novo Veículo' ?></h3>
    <form method="post" action="veiculos_acao.php">
        <input type="hidden" name="id" value="<?= $veiculo['id_veiculo'] ?? '' ?>">

        <input type="text" name="placa" placeholder="Placa" value="<?= htmlspecialchars($veiculo['placa'] ?? '') ?>" required>
        <input type="text" name="marca" placeholder="Marca" value="<?= htmlspecialchars($veiculo['marca'] ?? '') ?>" required>
        <input type="text" name="modelo" placeholder="Modelo" value="<?= htmlspecialchars($veiculo['modelo'] ?? '') ?>" required>
        <input type="number" name="ano" placeholder="Ano" value="<?= $veiculo['ano'] ?? '' ?>" required>
        <input type="text" name="cor" placeholder="Cor" value="<?= htmlspecialchars($veiculo['cor'] ?? '') ?>">

        <select name="id_categoria" required>
            <option value="">— Selecione a Categoria —</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id_categoria'] ?>" <?= ($veiculo && $veiculo['id_categoria'] == $cat['id_categoria']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nome_categoria']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="id_loja_atual" required>
            <option value="">— Selecione a Loja Atual —</option>
            <?php foreach ($lojas as $l): ?>
                <option value="<?= $l['id_loja'] ?>" <?= ($veiculo && $veiculo['id_loja_atual'] == $l['id_loja']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars("{$l['nome']} ({$l['cidade']}/{$l['estado']})") ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="status" required>
            <option value="Disponível" <?= ($veiculo && $veiculo['status'] == 'Disponível') ? 'selected' : '' ?>>Disponível</option>
            <option value="Reservado" <?= ($veiculo && $veiculo['status'] == 'Reservado') ? 'selected' : '' ?>>Reservado</option>
            <option value="Locado" <?= ($veiculo && $veiculo['status'] == 'Locado') ? 'selected' : '' ?>>Locado</option>
            <option value="Manutenção" <?= ($veiculo && $veiculo['status'] == 'Manutenção') ? 'selected' : '' ?>>Manutenção</option>
        </select>

        <button type="submit"><?= $veiculo ? 'Atualizar' : 'Cadastrar' ?></button>
    </form>

    <h3>Lista de Veículos</h3>
    <table>
        <tr>
            <th>Placa</th>
            <th>Marca/Modelo</th>
            <th>Ano</th>
            <th>Categoria</th>
            <th>Loja</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($veiculos as $v): ?>
            <tr>
                <td><?= htmlspecialchars($v['placa']) ?></td>
                <td><?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?></td>
                <td><?= $v['ano'] ?></td>
                <td><?= htmlspecialchars($v['nome_categoria'] ?? '—') ?></td>
                <td><?= htmlspecialchars($v['loja_nome'] ?? '—') ?></td>
                <td><?= htmlspecialchars($v['status']) ?></td>
                <td>
                    <a href="?editar=<?= $v['id_veiculo'] ?>">Editar</a> |
                    <a href="veiculos_acao.php?excluir=<?= $v['id_veiculo'] ?>" onclick="return confirm('Tem certeza?')">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>