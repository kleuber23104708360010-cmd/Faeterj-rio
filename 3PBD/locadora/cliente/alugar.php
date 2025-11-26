<?php
session_start();
if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../index.php');
    exit;
}

require '../config/db.php';

$id_veiculo = $_GET['id'] ?? null;
if (!$id_veiculo) {
    die("Veículo não especificado.");
}

$stmt = $pdo->prepare("
    SELECT v.id_veiculo, v.marca, v.modelo, v.ano, l.id_loja, l.cidade, l.estado
    FROM veiculos v
    JOIN lojas l ON v.id_loja_atual = l.id_loja
    WHERE v.id_veiculo = ? AND v.status = 'Disponível'
");
$stmt->execute([$id_veiculo]);
$veiculo = $stmt->fetch();

if (!$veiculo) {
    die("Veículo não disponível para locação.");
}

$stmt_lojas = $pdo->query("SELECT id_loja, nome, cidade, estado FROM lojas ORDER BY cidade");
$lojas = $stmt_lojas->fetchAll();

$dias = 7;
$com_motorista = false;
$loja_retirada_id = $veiculo['id_loja'];
$valor_exibido = null;
$taxa_distante_valor = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loja_retirada_id = (int)($_POST['loja_retirada'] ?? $veiculo['id_loja']);
    $dias = (int)($_POST['dias'] ?? 7);
    $com_motorista = !empty($_POST['motorista']);

    $valor_diaria = $com_motorista ? 80 : 50;
    $valor_exibido = $dias * $valor_diaria;
    $taxa_distante_valor = 0;

    if ($loja_retirada_id != $veiculo['id_loja']) {
        $stmt = $pdo->prepare("SELECT cidade FROM lojas WHERE id_loja = ?");
        $stmt->execute([$loja_retirada_id]);
        $loja_ret = $stmt->fetch();
        if ($loja_ret && $loja_ret['cidade'] === $veiculo['cidade']) {
            $taxa_distante_valor = 50;
            $valor_exibido += $taxa_distante_valor;
        }
    }

    $data_ret = date('Y-m-d');
    $data_dev = date('Y-m-d', strtotime("+$dias days"));

    $stmt = $pdo->prepare("
        INSERT INTO locacoes (
            id_cliente, id_veiculo, id_loja_retirada, id_loja_devolucao,
            data_retirada, data_devolucao_prevista, periodo_dias,
            com_motorista, taxa_distante, valor_total, status_locacao
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Ativa')
    ");
    
    $stmt->execute([
        $_SESSION['cliente_id'],
        $id_veiculo,
        $loja_retirada_id,
        $loja_retirada_id,
        $data_ret,
        $data_dev,
        $dias,
        $com_motorista ? 1 : 0,
        $taxa_distante_valor,
        $valor_exibido
    ]);

    $pdo->prepare("UPDATE veiculos SET status = 'Locado' WHERE id_veiculo = ?")
        ->execute([$id_veiculo]);

    header("Location: painel.php?locado=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['loja_retirada'])) {
    $loja_retirada_id = (int)$_GET['loja_retirada'];
    $dias = (int)($_GET['dias'] ?? 7);
    $com_motorista = !empty($_GET['motorista']);

    $valor_diaria = $com_motorista ? 80 : 50;
    $valor_exibido = $dias * $valor_diaria;
    $taxa_distante_valor = 0;

    if ($loja_retirada_id != $veiculo['id_loja']) {
        $stmt = $pdo->prepare("SELECT cidade FROM lojas WHERE id_loja = ?");
        $stmt->execute([$loja_retirada_id]);
        $loja_ret = $stmt->fetch();
        if ($loja_ret && $loja_ret['cidade'] === $veiculo['cidade']) {
            $taxa_distante_valor = 50;
            $valor_exibido += $taxa_distante_valor;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Alugar Veículo</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>Alugar: <?= htmlspecialchars($veiculo['marca'] . ' ' . $veiculo['modelo']) ?></h2>

    <?php if ($valor_exibido !== null): ?>
        <div style="background:#e8f4fc; padding:12px; margin:15px 0; border-radius:6px; border-left:4px solid #3498db;">
            <strong>Valor estimado da locação: R$ <?= number_format($valor_exibido, 2, ',', '.') ?></strong>
            <?php if ($taxa_distante_valor > 0): ?>
                <br><small style="color:#2c3e50;">Inclui taxa de R$ <?= $taxa_distante_valor ?> por retirada em outra loja da mesma cidade</small>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form method="get" id="form-preview">
        <input type="hidden" name="id" value="<?= $id_veiculo ?>">

        <label>Loja de Retirada:</label>
        <select name="loja_retirada" onchange="this.form.submit()">
            <?php foreach ($lojas as $l): ?>
                <option value="<?= $l['id_loja'] ?>" <?= ($loja_retirada_id == $l['id_loja']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars("{$l['nome']} - {$l['cidade']}/{$l['estado']}") ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Período (dias):</label>
        <select name="dias" onchange="this.form.submit()">
            <option value="7" <?= ($dias == 7) ? 'selected' : '' ?>>7 dias</option>
            <option value="15" <?= ($dias == 15) ? 'selected' : '' ?>>15 dias</option>
            <option value="30" <?= ($dias == 30) ? 'selected' : '' ?>>30 dias</option>
        </select>

        <label style="display: flex; align-items: center; gap: 8px; margin: 10px 0;">
            <input type="checkbox" name="motorista" <?= $com_motorista ? 'checked' : '' ?> onchange="this.form.submit()">
            Incluir motorista (+R$30/dia)
        </label>

        <button type="submit" formaction="" formmethod="post">Confirmar Locação</button>
    </form>

    <a href="painel.php">Voltar</a>
</div>
</body>
</html>