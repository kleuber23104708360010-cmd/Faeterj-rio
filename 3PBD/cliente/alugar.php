<?php
session_start();
if (!isset($_SESSION['cliente_id'])) header('Location: ../index.php');

require '../config/db.php';

$id_veiculo = $_GET['id'] ?? 0;

// Pegar dados do veículo
$stmt = $pdo->prepare("
    SELECT v.id_veiculo, v.marca, v.modelo, l.id_loja, l.cidade, l.estado
    FROM veiculos v
    JOIN lojas l ON v.id_loja_atual = l.id_loja
    WHERE v.id_veiculo = ? AND v.status = 'Disponível'
");
$stmt->execute([$id_veiculo]);
$veiculo = $stmt->fetch();

if (!$veiculo) {
    die("Veículo não disponível.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loja_retirada = $_POST['loja_retirada'];
    $dias = (int)$_POST['dias'];
    $com_motorista = isset($_POST['motorista']) ? 1 : 0;

    // Calcular valor (simplificado: R$50/dia sem motorista, R$80 com)
    $valor_diaria = $com_motorista ? 80 : 50;
    $valor_total = $dias * $valor_diaria;

    // Verificar se é outra loja na mesma cidade → taxa
    $taxa_distante = 0;
    if ((int)$loja_retirada !== (int)$veiculo['id_loja']) {
        $stmt = $pdo->prepare("SELECT cidade FROM lojas WHERE id_loja = ?");
        $stmt->execute([$loja_retirada]);
        $loja_ret = $stmt->fetch();
        if ($loja_ret && $loja_ret['cidade'] === $veiculo['cidade']) {
            $taxa_distante = 50; // taxa fixa
            $valor_total += $taxa_distante;
        }
    }

    $data_ret = date('Y-m-d');
    $data_dev = date('Y-m-d', strtotime("+$dias days"));

    // Inserir locação
    $stmt = $pdo->prepare("
        INSERT INTO locacoes (
            id_cliente, id_veiculo, id_loja_retirada, id_loja_devolucao,
            data_retirada, data_devolucao_prevista, periodo_dias,
            com_motorista, taxa_distante, valor_total
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['cliente_id'], $id_veiculo, $loja_retirada, $loja_retirada,
        $data_ret, $data_dev, $dias, $com_motorista, $taxa_distante, $valor_total
    ]);

    // Atualizar status do veículo
    $pdo->prepare("UPDATE veiculos SET status = 'Locado' WHERE id_veiculo = ?")
        ->execute([$id_veiculo]);

    // Redirecionar com sucesso
    header("Location: painel.php?locado=1");
    exit;
}

// Listar lojas para retirada (mesma cidade ou qualquer)
$stmt = $pdo->query("SELECT id_loja, nome, cidade, estado FROM lojas ORDER BY cidade");
$lojas = $stmt->fetchAll();
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
    <form method="post">
        <label>Loja de Retirada:</label>
        <select name="loja_retirada" required>
            <?php foreach ($lojas as $l): ?>
                <option value="<?= $l['id_loja'] ?>">
                    <?= htmlspecialchars("{$l['nome']} - {$l['cidade']}/{$l['estado']}") ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Período (dias):</label>
        <select name="dias" required>
            <option value="7">7 dias</option>
            <option value="15">15 dias</option>
            <option value="30">30 dias</option>
        </select>

        <label>
            <input type="checkbox" name="motorista"> Incluir motorista (+R$30/dia)
        </label>

        <button type="submit">Confirmar Locação</button>
    </form>
    <a href="painel.php">Voltar</a>
</div>
</body>
</html>