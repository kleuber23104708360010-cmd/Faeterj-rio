<?php
session_start();
if (!isset($_SESSION['cliente_id'])) header('Location: ../index.php');

require '../config/db.php';

// Listar veículos disponíveis
$stmt = $pdo->query("
    SELECT v.id_veiculo, v.marca, v.modelo, v.ano, l.cidade, l.estado, cat.nome_categoria
    FROM veiculos v
    JOIN lojas l ON v.id_loja_atual = l.id_loja
    JOIN categorias_veiculo cat ON v.id_categoria = cat.id_categoria
    WHERE v.status = 'Disponível'
    ORDER BY l.cidade, v.marca
");
$veiculos = $stmt->fetchAll();
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
</div>
</body>
</html>