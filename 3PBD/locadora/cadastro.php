<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Criar Conta - Locadora</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h2>Criar Conta</h2>

    <?php if (isset($_GET['erro'])): ?>
        <p style="color: red;">
            <?php
            if ($_GET['erro'] == 'email') echo "E-mail já cadastrado.";
            elseif ($_GET['erro'] == 'cpf') echo "CPF já cadastrado.";
            elseif ($_GET['erro'] == 'cnh') echo "CNH já cadastrada.";
            else echo "Erro ao cadastrar. Verifique os dados e tente novamente.";
            ?>
        </p>
    <?php endif; ?>

    <form action="registrar.php" method="post">
        <input type="text" name="nome" placeholder="Nome completo" required>
        <input type="text" name="cpf" placeholder="CPF (ex: 123.456.789-00)" required>
        <input type="text" name="cnh" placeholder="CNH (11 dígitos)" required>
        <input type="email" name="email" placeholder="E-mail" required>
        <input type="tel" name="telefone" placeholder="Telefone (ex: (11) 99999-9999)">
        <input type="text" name="endereco" placeholder="Endereço completo">
        <button type="submit">Criar Conta</button>
    </form>
    <p>Já tem conta? <a href="index.php">Faça login</a>.</p>
</div>
</body>
</html>