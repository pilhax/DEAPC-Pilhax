<?php
/**
 * novoregisto.php — Registo de novo utilizador (CLI07)
 * POST: nome, apelido, email, password, password2, data_nascimento, csrf_token
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

iniciar_sessao();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.html');
    exit;
}

verificar_csrf();

$nome            = trim($_POST['nome']            ?? '');
$apelido         = trim($_POST['apelido']         ?? '');
$email           = trim($_POST['email']           ?? '');
$password        = $_POST['password']             ?? '';
$password2       = $_POST['password2']            ?? '';
$data_nascimento = trim($_POST['data_nascimento'] ?? '');

// Validações
if ($nome === '' || $apelido === '' || $email === '' || $password === '') {
    redirecionar_erro('../login.html', 'Preencha todos os campos obrigatórios.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirecionar_erro('../login.html', 'Email inválido.');
}
if (strlen($password) < 8) {
    redirecionar_erro('../login.html', 'A palavra-passe deve ter pelo menos 8 caracteres.');
}
if ($password !== $password2) {
    redirecionar_erro('../login.html', 'As palavras-passe não coincidem.');
}

try {
    $pdo = obter_ligacao();
    inicializar_bd();

    // Verificar se o email já existe
    $stmt = $pdo->prepare("SELECT id FROM utilizadores WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        redirecionar_erro('../login.html', 'Esse email já está registado.');
    }

    $pdo->prepare("
        INSERT INTO utilizadores (nome, apelido, email, password_hash, perfil, data_nascimento)
        VALUES (?, ?, ?, ?, 'cliente', ?)
    ")->execute([$nome, $apelido, $email, password_hash($password, PASSWORD_DEFAULT), $data_nascimento ?: null]);

    $id = (int) $pdo->lastInsertId();

    session_regenerate_id(true);
    $_SESSION['utilizador_id'] = $id;
    $_SESSION['nome']          = $nome . ' ' . $apelido;
    $_SESSION['perfil']        = 'cliente';
    $_SESSION['email']         = $email;

    registar_acesso($id);

    header('Location: ../conta.html?sucesso=Conta+criada+com+sucesso');
    exit;

} catch (PDOException $e) {
    redirecionar_erro('../login.html', 'Erro ao criar conta. Tente novamente.');
}
