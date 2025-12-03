<?php
require '../config/db.php';

$id_locacao = $_GET['id'] ?? null;

$stmt = $pdo->prepare("
    SELECT loc.*, c.nome AS cliente, v.marca, v.modelo, v.placa
    FROM locacoes loc
    JOIN clientes c ON loc.id_cliente = c.id_cliente
    JOIN veiculos v ON loc.id_veiculo = v.id_veiculo
    WHERE loc.id_locacao = ? AND loc.status_locacao = 'Ativa'
");
$stmt->execute([$id_locacao]);
$loc = $stmt->fetch();

if (!$loc) die("Locação não encontrada ou já concluída.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $km = $_POST['km'] ?? 0;
    $observacoes = $_POST['observacoes'] ?? '';
    $itens = $_POST['itens'] ?? [];

    $stmt = $pdo->prepare("INSERT INTO checkups (id_locacao, km_devolucao, observacoes) VALUES (?, ?, ?)");
    $stmt->execute([$id_locacao, $km, $observacoes]);
    $id_checkup = $pdo->lastInsertId();

    $valor_adicional = 0;
    foreach ($itens as $item) {
        if (!empty($item['descricao'])) {
            $tipo = $item['tipo'];
            $desc = $item['descricao'];
            $valor = (float)($item['valor'] ?? 0);
            $valor_adicional += $valor;

            $stmt = $pdo->prepare("INSERT INTO itens_checkup (id_checkup, tipo_item, descricao, valor_cobranca) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id_checkup, $tipo, $desc, $valor]);
        }
    }

    $novo_total = $loc['valor_total'] + $valor_adicional;
    $pdo->prepare("UPDATE locacoes SET valor_total = ?, status_locacao = 'Concluída' WHERE id_locacao = ?")
        ->execute([$novo_total, $id_locacao]);

    $pdo->prepare("UPDATE veiculos SET status = 'Disponível' WHERE id_veiculo = ?")
        ->execute([$loc['id_veiculo']]);

    header("Location: locacoes_ativas.php?concluida=1");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Concluir Locação</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>Concluir Locação #<?= $loc['id_locacao'] ?></h2>
    <p><strong>Cliente:</strong> <?= htmlspecialchars($loc['cliente']) ?></p>
    <p><strong>Veículo:</strong> <?= htmlspecialchars($loc['marca'] . ' ' . $loc['modelo'] . ' (' . $loc['placa'] . ')') ?></p>
    <p><strong>Valor original:</strong> R$ <?= number_format($loc['valor_total'], 2, ',', '.') ?></p>

    <form method="post">
        <label>Quilometragem na devolução:</label>
        <input type="number" name="km" required>

        <label>Observações gerais:</label>
        <textarea name="observacoes" rows="3"></textarea>

        <h3>Itens Adicionais (Avarias, Multas, etc.)</h3>
        <div id="itens">
            <div>
                <select name="itens[0][tipo]">
                    <option value="Avaria">Avaria</option>
                    <option value="Multa">Multa</option>
                    <option value="Limpeza">Limpeza</option>
                    <option value="Outros">Outros</option>
                </select>
                <input type="text" name="itens[0][descricao]" placeholder="Descrição" required>
                <input type="number" name="itens[0][valor]" placeholder="Valor (R$)" step="0.01" min="0" required>
            </div>
        </div>
        <button type="button" onclick="addCampo()">+ Adicionar item</button>

        <button type="submit">Concluir Locação</button>
    </form>

    <a href="locacoes_ativas.php">← Voltar</a>
</div>

<script>
let contador = 1;
function addCampo() {
    const div = document.createElement('div');
    div.innerHTML = `
        <select name="itens[${contador}][tipo]">
            <option value="Avaria">Avaria</option>
            <option value="Multa">Multa</option>
            <option value="Limpeza">Limpeza</option>
            <option value="Outros">Outros</option>
        </select>
        <input type="text" name="itens[${contador}][descricao]" placeholder="Descrição" required>
        <input type="number" name="itens[${contador}][valor]" placeholder="Valor (R$)" step="0.01" min="0" required>
    `;
    document.getElementById('itens').appendChild(div);
    contador++;
}
</script>
</body>
</html>