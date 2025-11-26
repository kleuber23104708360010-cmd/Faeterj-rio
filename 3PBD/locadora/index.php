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
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Entrar</button>
    </form>
    <p>Ainda não tem conta? <a href="cadastro.php">Crie uma agora</a>.</p>
</div>
</body>
</html>