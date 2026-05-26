<?php
/**
 * admin_cancelar_evento.php — Cancelar evento (ADM03)
 * POST: id, csrf_token
 * Devolve JSON.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

iniciar_sessao();
verificar_permissao('administrador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_resposta(false, null, 'Método não permitido.');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    json_resposta(false, null, 'ID inválido.');
}

try {
    $pdo = obter_ligacao();

    $ev = $pdo->prepare("SELECT estado FROM eventos WHERE id = ?");
    $ev->execute([$id]);
    $evento = $ev->fetch();
    if (!$evento) {
        json_resposta(false, null, 'Evento não encontrado.');
    }
    if ($evento['estado'] === 'cancelado') {
        json_resposta(false, null, 'Evento já está cancelado.');
    }

    $pdo->beginTransaction();

    // Cancelar evento
    $pdo->prepare("UPDATE eventos SET estado = 'cancelado' WHERE id = ?")
        ->execute([$id]);

    // Cancelar compras pendentes e registar compradores a notificar
    $compradores = $pdo->prepare("
        SELECT DISTINCT email_cliente, nome_cliente FROM compras
        WHERE evento_id = ? AND estado = 'confirmado'
    ");
    $compradores->execute([$id]);
    $lista = $compradores->fetchAll();

    $pdo->prepare("UPDATE compras SET estado = 'cancelado' WHERE evento_id = ?")
        ->execute([$id]);

    $pdo->commit();

    // Em produção: enviar email aos compradores ($lista)
    json_resposta(true, [
        'compradores_notificados' => count($lista),
        'emails' => array_column($lista, 'email_cliente'),
    ]);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    json_resposta(false, null, 'Erro ao cancelar evento.');
}
