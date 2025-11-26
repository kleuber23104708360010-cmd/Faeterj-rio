<?php
session_start();
require 'config/db.php';


$nome = trim($_POST['nome'] ?? '');
$cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
$cnh = preg_replace('/\D/', '', $_POST['cnh'] ?? ''); 
$email = trim($_POST['email'] ?? '');
$telefone = $_POST['telefone'] ?? '';
$endereco = trim($_POST['endereco'] ?? '');


if (empty($nome) || empty($cpf) || empty($cnh) || empty($email)) {
    header('Location: cadastro.php?erro=1');
    exit;
}


if (strlen($cnh) !== 11) {
    header('Location: cadastro.php?erro=1');
    exit;
}


$cpf_formatado = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
$cnh_formatada = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cnh); 


$stmt = $pdo->prepare("
    SELECT id_cliente, email, cpf, cnh 
    FROM clientes 
    WHERE email = ? OR cpf = ? OR cnh = ?
");
$stmt->execute([$email, $cpf_formatado, $cnh_formatada]);
$existente = $stmt->fetch();

if ($existente) {
    if ($existente['email'] == $email) {
        header('Location: cadastro.php?erro=email');
    } elseif ($existente['cpf'] == $cpf_formatado) {
        header('Location: cadastro.php?erro=cpf');
    } elseif ($existente['cnh'] == $cnh_formatada) {
        header('Location: cadastro.php?erro=cnh');
    } else {
        header('Location: cadastro.php?erro=1');
    }
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO clientes (nome, cpf, cnh, email, telefone, endereco)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$nome, $cpf_formatado, $cnh_formatada, $email, $telefone, $endereco]);

    $_SESSION['cliente_id'] = $pdo->lastInsertId();
    $_SESSION['cliente_nome'] = $nome;

    header('Location: cliente/painel.php');
    exit;
} catch (Exception $e) {
    error_log("Erro no cadastro: " . $e->getMessage());
    header('Location: cadastro.php?erro=1');
    exit;
}
?>