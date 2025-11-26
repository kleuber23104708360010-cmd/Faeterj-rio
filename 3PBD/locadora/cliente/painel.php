<?php
session_start();
if (!isset($_SESSION['cliente_id'])) header('Location: ../index.php');

require '../config/db.php';
$id_cliente = $_SESSION['cliente_id'];

$stmt = $pdo->query("
    SELECT v.id_veiculo, v.marca, v.modelo, v.ano, l.cidade, l.estado, cat.nome_categoria
    FROM veiculos v
    JOIN lojas l ON v.id_loja_atual = l.id_loja
    JOIN categorias_veiculo cat ON v.id_categoria = cat.id_categoria
    WHERE v.status = 'Disponível'
    ORDER BY l.cidade, v.marca
");
$veiculos = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT loc.id_locacao, v.marca, v.modelo, loc.data_retirada, loc.data_devolucao_prevista,
           loc.valor_total
    FROM locacoes loc
    JOIN veiculos v ON loc.id_veiculo = v.id_veiculo
    WHERE loc.id_cliente = ? AND loc.status_locacao = 'Concluída'
    ORDER BY loc.data_retirada DESC
");
$stmt->execute([$id_cliente]);
$locacoes_passadas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Área do Cliente</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <div class="nav">
        <a href="painel.php">Início</a>
        <a href="../logout.php">Sair</a>
    </div>
    <h2>Olá, <?= htmlspecialchars($_SESSION['cliente_nome']) ?>!</h2>

    <?php if (isset($_GET['locado'])): ?>
        <p style="color: green; font-weight: bold;">Locação realizada com sucesso!</p>
    <?php endif; ?>

    <h3>Veículos Disponíveis para Locação</h3>
    <?php if ($veiculos): ?>
        <table>
            <tr>
                <th>Marca/Modelo</th>
                <th>Ano</th>
                <th>Categoria</th>
                <th>Local</th>
                <th>Ação</th>
            </tr>
            <?php foreach ($veiculos as $v): ?>
                <tr>
                    <td><?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?></td>
                    <td><?= $v['ano'] ?></td>
                    <td><?= htmlspecialchars($v['nome_categoria']) ?></td>
                    <td><?= htmlspecialchars($v['cidade'] . '/' . $v['estado']) ?></td>
                    <td><a href="alugar.php?id=<?= $v['id_veiculo'] ?>">Alugar</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Nenhum veículo disponível no momento.</p>
    <?php endif; ?>

    <?php if ($locacoes_passadas): ?>
        <h3>Locações Concluídas</h3>
        <table>
            <tr>
                <th>Veículo</th>
                <th>Retirada</th>
                <th>Devolução Prevista</th>
                <th>Valor Final</th>
            </tr>
            <?php foreach ($locacoes_passadas as $l): ?>
                <tr>
                    <td><?= htmlspecialchars($l['marca'] . ' ' . $l['modelo']) ?></td>
                    <td><?= $l['data_retirada'] ?></td>
                    <td><?= $l['data_devolucao_prevista'] ?></td>
                    <td><strong>R$ <?= number_format($l['valor_total'], 2, ',', '.') ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
</body>
</html>