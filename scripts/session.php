<?php
/**
 * Funções auxiliares de sessão, CSRF e respostas JSON.
 */

require_once __DIR__ . '/db.php';

function iniciar_sessao(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function sessao_utilizador(): ?array {
    iniciar_sessao();
    if (!empty($_SESSION['utilizador_id'])) {
        return [
            'id'     => (int) $_SESSION['utilizador_id'],
            'nome'   => $_SESSION['nome']   ?? '',
            'perfil' => $_SESSION['perfil'] ?? 'cliente',
        ];
    }
    return null;
}

function verificar_sessao(string $redirect = '../login.html'): array {
    $u = sessao_utilizador();
    if (!$u) {
        header('Location: ' . $redirect);
        exit;
    }
    return $u;
}

function verificar_permissao(string $perfil_requerido, string $redirect = '../index.html'): array {
    $u = verificar_sessao();
    if ($u['perfil'] !== $perfil_requerido) {
        header('Location: ' . $redirect);
        exit;
    }
    return $u;
}

function registar_acesso(int $utilizador_id): void {
    try {
        $pdo = obter_ligacao();
        $pdo->prepare("UPDATE utilizadores SET ultimo_acesso = datetime('now') WHERE id = ?")
            ->execute([$utilizador_id]);
        $pdo->prepare("INSERT INTO acessos (utilizador_id, ip) VALUES (?, ?)")
            ->execute([$utilizador_id, $_SERVER['REMOTE_ADDR'] ?? null]);
    } catch (Exception $e) {
        // Falha silenciosa — não bloqueia o login
    }
}

function json_resposta(bool $sucesso, $dados = null, string $erro = ''): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'sucesso' => $sucesso,
        'dados'   => $dados,
        'erro'    => $erro,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function csrf_token(): string {
    iniciar_sessao();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verificar_csrf(): void {
    iniciar_sessao();
    $token = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Acesso negado: token inválido.');
    }
}

function redirecionar_erro(string $url, string $mensagem): void {
    header('Location: ' . $url . '?erro=' . urlencode($mensagem));
    exit;
}

function redirecionar_sucesso(string $url, string $mensagem = ''): void {
    $sufixo = $mensagem ? '?sucesso=' . urlencode($mensagem) : '';
    header('Location: ' . $url . $sufixo);
    exit;
}
