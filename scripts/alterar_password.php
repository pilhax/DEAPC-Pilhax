<?php
/**
 * alterar_password.php — Alteração de palavra-passe (Q8a)
 * POST: password_atual, password_nova, password_confirma, csrf_token
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

$atual     = $_POST['password_atual']     ?? '';
$nova      = $_POST['password_nova']      ?? '';
$confirma  = $_POST['password_confirma']  ?? '';

if ($nova !== $confirma) {
    redirecionar_erro('../conta.html', 'As palavras-passe não coincidem.');
}
if (strlen($nova) < 8) {
    redirecionar_erro('../conta.html', 'A nova palavra-passe deve ter pelo menos 8 caracteres.');
}

try {
    $pdo  = obter_ligacao();
    $stmt = $pdo->prepare("SELECT password_hash FROM utilizadores WHERE id = ?");
    $stmt->execute([$utilizador['id']]);
    $row  = $stmt->fetch();

    if (!$row || !password_verify($atual, $row['password_hash'])) {
        redirecionar_erro('../conta.html', 'Palavra-passe atual incorreta.');
    }

    $pdo->prepare("UPDATE utilizadores SET password_hash = ? WHERE id = ?")
        ->execute([password_hash($nova, PASSWORD_DEFAULT), $utilizador['id']]);

    redirecionar_sucesso('../conta.html', 'Palavra-passe alterada com sucesso.');

} catch (PDOException $e) {
    redirecionar_erro('../conta.html', 'Erro ao alterar palavra-passe.');
}
