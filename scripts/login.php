<?php
/**
 * login.php — Autenticação de utilizador (CLI07, autenticação)
 * POST: email, password, csrf_token
 * Redireciona conforme o perfil ou devolve erro.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

iniciar_sessao();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.html');
    exit;
}

verificar_csrf();

$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    redirecionar_erro('../login.html', 'Preencha o email e a palavra-passe.');
}

try {
    $pdo  = obter_ligacao();
    $stmt = $pdo->prepare("SELECT * FROM utilizadores WHERE email = ?");
    $stmt->execute([$email]);
    $utilizador = $stmt->fetch();

    if (!$utilizador || !password_verify($password, $utilizador['password_hash'])) {
        redirecionar_erro('../login.html', 'Email ou palavra-passe incorretos.');
    }

    if ($utilizador['estado'] === 'suspenso') {
        redirecionar_erro('../login.html', 'Conta suspensa. Contacte o administrador.');
    }

    // Iniciar sessão
    session_regenerate_id(true);
    $_SESSION['utilizador_id'] = $utilizador['id'];
    $_SESSION['nome']          = $utilizador['nome'] . ' ' . $utilizador['apelido'];
    $_SESSION['perfil']        = $utilizador['perfil'];
    $_SESSION['email']         = $utilizador['email'];

    registar_acesso((int) $utilizador['id']);

    // Redirecionar conforme perfil
    match ($utilizador['perfil']) {
        'administrador' => header('Location: ../admin-dashboard.html'),
        'vendedor'      => header('Location: ../vendedor.html'),
        default         => header('Location: ../conta.html'),
    };
    exit;

} catch (PDOException $e) {
    redirecionar_erro('../login.html', 'Erro interno. Tente novamente.');
}
