<?php
/**
 * perfil.php — Atualiza dados pessoais do utilizador (CLI07)
 * POST: nome, apelido, email, telefone, nif, data_nascimento, csrf_token
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

iniciar_sessao();
$utilizador = verificar_sessao();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../conta.html');
    exit;
}

verificar_csrf();

$nome            = trim($_POST['nome']            ?? '');
$apelido         = trim($_POST['apelido']         ?? '');
$email           = trim($_POST['email']           ?? '');
$telefone        = trim($_POST['telefone']        ?? '');
$nif             = trim($_POST['nif']             ?? '');
$data_nascimento = trim($_POST['data_nascimento'] ?? '');

if ($nome === '' || $apelido === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirecionar_erro('../conta.html', 'Dados inválidos.');
}

try {
    $pdo = obter_ligacao();

    // Verificar conflito de email com outro utilizador
    $stmt = $pdo->prepare("SELECT id FROM utilizadores WHERE email = ? AND id != ?");
    $stmt->execute([$email, $utilizador['id']]);
    if ($stmt->fetch()) {
        redirecionar_erro('../conta.html', 'Esse email já está em uso.');
    }

    $pdo->prepare("
        UPDATE utilizadores
        SET nome = ?, apelido = ?, email = ?, telefone = ?, nif = ?, data_nascimento = ?
        WHERE id = ?
    ")->execute([$nome, $apelido, $email, $telefone ?: null, $nif ?: null,
                 $data_nascimento ?: null, $utilizador['id']]);

    $_SESSION['nome']  = $nome . ' ' . $apelido;
    $_SESSION['email'] = $email;

    redirecionar_sucesso('../conta.html', 'Perfil atualizado com sucesso.');

} catch (PDOException $e) {
    redirecionar_erro('../conta.html', 'Erro ao guardar dados.');
}
