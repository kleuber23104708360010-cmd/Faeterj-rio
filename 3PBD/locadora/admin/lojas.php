<?php
require '../config/db.php';

if (isset($_GET['sucesso'])) {
    if ($_GET['sucesso'] === 'salvo') {
        $mensagem_sucesso = "Veículo salvo com sucesso!";
    } elseif ($_GET['sucesso'] === 'excluido') {
        $mensagem_sucesso = "Veículo excluído com sucesso!";
    }
}

$loja = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM lojas WHERE id_loja = ?");
    $stmt->execute([$_GET['editar']]);
    $loja = $stmt->fetch();
}

$stmt = $pdo->query("SELECT * FROM lojas ORDER BY cidade, nome");
$lojas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lojas - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>Lojas</h2>
    <a href="painel.php">← Voltar ao Painel</a>

    <h3><?= $loja ? 'Editar Loja' : 'Adicionar Nova Loja' ?></h3>
    <form method="post" action="lojas_acao.php">
        <input type="hidden" name="id" value="<?= $loja['id_loja'] ?? '' ?>">

        <input type="text" name="nome" placeholder="Nome da Loja" value="<?= htmlspecialchars($loja['nome'] ?? '') ?>" required>
        <input type="text" name="endereco" placeholder="Endereço" value="<?= htmlspecialchars($loja['endereco'] ?? '') ?>" required>
        <input type="text" name="cidade" placeholder="Cidade" value="<?= htmlspecialchars($loja['cidade'] ?? '') ?>" required>
        <input type="text" name="estado" placeholder="Estado (ex: SP)" maxlength="2" value="<?= htmlspecialchars($loja['estado'] ?? '') ?>" required>

        <select name="tipo_loja" required>
            <option value="">— Tipo —</option>
            <option value="Aeroporto" <?= ($loja && $loja['tipo_loja'] == 'Aeroporto') ? 'selected' : '' ?>>Aeroporto</option>
            <option value="Cidade" <?= ($loja && $loja['tipo_loja'] == 'Cidade') ? 'selected' : '' ?>>Cidade</option>
        </select>

        <button type="submit"><?= $loja ? 'Atualizar' : 'Cadastrar' ?></button>
    </form>

    <h3>Lista de Lojas</h3>
    <table>
        <tr>
            <th>Nome</th>
            <th>Endereço</th>
            <th>Cidade/UF</th>
            <th>Tipo</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($lojas as $l): ?>
            <tr>
                <td><?= htmlspecialchars($l['nome']) ?></td>
                <td><?= htmlspecialchars($l['endereco']) ?></td>
                <td><?= htmlspecialchars($l['cidade'] . '/' . $l['estado']) ?></td>
                <td><?= htmlspecialchars($l['tipo_loja']) ?></td>
                <td>
                    <a href="?editar=<?= $l['id_loja'] ?>">Editar</a> |
                    <a href="lojas_acao.php?excluir=<?= $l['id_loja'] ?>" onclick="return confirm('Excluir esta loja?')">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>