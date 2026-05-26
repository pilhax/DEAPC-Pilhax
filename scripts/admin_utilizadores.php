<?php
/**
 * admin_utilizadores.php — Gestão de utilizadores (ADM06)
 * GET:  lista utilizadores (com filtros opcionais: pesquisa, perfil, estado)
 * POST: ação (suspender | ativar | alterar_perfil), id, csrf_token
 * Devolve JSON.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

iniciar_sessao();
$admin = verificar_permissao('administrador');
$pdo   = obter_ligacao();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verificar_csrf();
    $acao = trim($_POST['acao'] ?? '');
    $id   = (int) ($_POST['id'] ?? 0);

    if ($id <= 0 || $id === $admin['id']) {
        json_resposta(false, null, 'Operação inválida.');
    }

    try {
        if ($acao === 'suspender') {
            $pdo->prepare("UPDATE utilizadores SET estado = 'suspenso' WHERE id = ?")
                ->execute([$id]);
            json_resposta(true, ['mensagem' => 'Utilizador suspenso.']);

        } elseif ($acao === 'ativar') {
            $pdo->prepare("UPDATE utilizadores SET estado = 'ativo' WHERE id = ?")
                ->execute([$id]);
            json_resposta(true, ['mensagem' => 'Utilizador ativado.']);

        } elseif ($acao === 'alterar_perfil') {
            $perfil = trim($_POST['perfil'] ?? '');
            if (!in_array($perfil, ['cliente', 'vendedor', 'administrador'], true)) {
                json_resposta(false, null, 'Perfil inválido.');
            }
            $pdo->prepare("UPDATE utilizadores SET perfil = ? WHERE id = ?")
                ->execute([$perfil, $id]);
            json_resposta(true, ['mensagem' => 'Perfil atualizado.']);
        } else {
            json_resposta(false, null, 'Ação desconhecida.');
        }
    } catch (PDOException $e) {
        json_resposta(false, null, 'Erro na operação.');
    }
}

// GET — listar utilizadores
$pesquisa = trim($_GET['pesquisa'] ?? '');
$perfil   = trim($_GET['perfil']   ?? '');
$estado   = trim($_GET['estado']   ?? '');

$where  = ['1=1'];
$params = [];

if ($pesquisa !== '') {
    $where[]  = "(nome || ' ' || apelido LIKE ? OR email LIKE ?)";
    $params[] = "%$pesquisa%";
    $params[] = "%$pesquisa%";
}
if ($perfil !== '') {
    $where[]  = "perfil = ?";
    $params[] = $perfil;
}
if ($estado !== '') {
    $where[]  = "estado = ?";
    $params[] = $estado;
}

$stmt = $pdo->prepare("
    SELECT id, nome, apelido, email, perfil, data_registo, ultimo_acesso, estado,
           (SELECT COUNT(*) FROM compras WHERE utilizador_id = utilizadores.id) AS total_compras,
           (SELECT COALESCE(SUM(total),0) FROM compras WHERE utilizador_id = utilizadores.id AND estado='confirmado') AS gasto_total
    FROM utilizadores
    WHERE " . implode(' AND ', $where) . "
    ORDER BY data_registo DESC
    LIMIT 200
");
$stmt->execute($params);

// Estatísticas
$stats = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(perfil = 'cliente')        AS clientes,
        SUM(perfil = 'vendedor')       AS vendedores,
        SUM(perfil = 'administrador')  AS administradores,
        SUM(estado = 'suspenso')       AS suspensos
    FROM utilizadores
")->fetch();

json_resposta(true, ['utilizadores' => $stmt->fetchAll(), 'stats' => $stats]);
