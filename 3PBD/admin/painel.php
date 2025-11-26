<?php
session_start();
// Para simplificação, não há login de admin. Em produção, adicione autenticação!
?>
<!DOCTYPE html>
<html>
<head>
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>Painel Administrativo</h2>
    <div class="nav">
        <a href="veiculos.php">Veículos</a>
        <a href="lojas.php">Lojas</a>
        <a href="relatorios.php">Relatórios</a>
        <a href="../logout.php">Sair</a>
    </div>
    <p>Bem-vindo ao painel de administração.</p>
</div>
</body>
</html>