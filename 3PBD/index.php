<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Locadora</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h2>Login</h2>
    <?php if (isset($_GET['erro'])): ?>
        <p style="color: red;">Usuário ou senha inválidos.</p>
    <?php endif; ?>
    <form action="login.php" method="post">
        <input type="email" name="email" placeholder="E-mail" required>
        <input type="password" name="senha" placeholder="Senha (qualquer valor)" required>
        <button type="submit">Entrar</button>
    </form>
    <p>Não tem conta? Qualquer e-mail cadastrado no banco funciona. Senha não é verificada (teste).</p>
</div>
</body>
</html>