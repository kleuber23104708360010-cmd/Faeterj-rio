<?php
require '../config/db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Relatórios</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>Relatórios Estatísticos</h2>
    <a href="painel.php">← Voltar</a>

    <h3>1. Veículos mais alugados por categoria/cidade</h3>
    <table>
        <tr><th>Cidade</th><th>Categoria</th><th>Marca/Modelo</th><th>Locações</th></tr>
        <?php
        $stmt = $pdo->query("SELECT * FROM vw_veiculos_mais_alugados LIMIT 10");
        while ($r = $stmt->fetch()): ?>
            <tr>
                <td><?= htmlspecialchars($r['cidade_loja']) ?></td>
                <td><?= htmlspecialchars($r['nome_categoria']) ?></td>
                <td><?= htmlspecialchars($r['marca'] . ' ' . $r['modelo']) ?></td>
                <td><?= $r['total_locacoes'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h3>2. Faturamento por loja/mês</h3>
    <table>
        <tr><th>Loja</th><th>Cidade</th><th>Mês</th><th>Faturamento</th></tr>
        <?php
        $stmt = $pdo->query("SELECT * FROM vw_faturamento_loja_mes LIMIT 10");
        while ($r = $stmt->fetch()): ?>
            <tr>
                <td><?= htmlspecialchars($r['nome_loja']) ?></td>
                <td><?= htmlspecialchars($r['cidade']) ?></td>
                <td><?= $r['mes_referencia'] ?></td>
                <td>R$ <?= number_format($r['faturamento_total'], 2, ',', '.') ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h3>3. Taxa de avarias por modelo</h3>
    <table>
        <tr><th>Marca/Modelo</th><th>Locações</th><th>Com Avaria</th><th>Taxa (%)</th></tr>
        <?php
        $stmt = $pdo->query("SELECT * FROM vw_taxa_avaria_por_modelo LIMIT 10");
        while ($r = $stmt->fetch()): ?>
            <tr>
                <td><?= htmlspecialchars($r['marca'] . ' ' . $r['modelo']) ?></td>
                <td><?= $r['total_locacoes'] ?></td>
                <td><?= $r['locacoes_com_avaria'] ?></td>
                <td><?= $r['taxa_avaria_percentual'] ?>%</td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>