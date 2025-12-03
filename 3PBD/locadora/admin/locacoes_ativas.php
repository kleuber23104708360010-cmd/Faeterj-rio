<?php
require '../config/db.php';

$stmt = $pdo->query("
    SELECT loc.id_locacao, c.nome AS cliente, v.marca, v.modelo, v.placa,
           loc.data_retirada, loc.data_devolucao_prevista, loc.valor_total
    FROM locacoes loc
    JOIN clientes c ON loc.id_cliente = c.id_cliente
    JOIN veiculos v ON loc.id_veiculo = v.id_veiculo
    WHERE loc.status_locacao = 'Ativa'
    ORDER BY loc.data_retirada DESC
");
$locacoes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Locações Ativas</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>Locações Ativas</h2>
    <a href="painel.php">← Voltar</a>

    <?php if (isset($_GET['concluida'])): ?>
        <p style="color: green;">Locação concluída com sucesso!</p>
    <?php endif; ?>

    <?php if ($locacoes): ?>
        <table>
            <tr>
                <th>Cliente</th>
                <th>Veículo</th>
                <th>Retirada</th>
                <th>Devolução Prevista</th>
                <th>Valor</th>
                <th>Ação</th>
            </tr>
            <?php foreach ($locacoes as $l): ?>
                <tr>
                    <td><?= htmlspecialchars($l['cliente']) ?></td>
                    <td><?= htmlspecialchars($l['marca'] . ' ' . $l['modelo'] . ' (' . $l['placa'] . ')') ?></td>
                    <td><?= $l['data_retirada'] ?></td>
                    <td><?= $l['data_devolucao_prevista'] ?></td>
                    <td>R$ <?= number_format($l['valor_total'], 2, ',', '.') ?></td>
                    <td><a href="concluir_locacao.php?id=<?= $l['id_locacao'] ?>">Concluir</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Nenhuma locação ativa.</p>
    <?php endif; ?>
</div>
</body>
</html>