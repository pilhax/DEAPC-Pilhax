<?php
/**
 * detalhe_evento.php — Detalhe de um evento com preços e disponibilidade (CLI03)
 * GET: id
 * Devolve JSON.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

inicializar_bd();
$pdo = obter_ligacao();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    json_resposta(false, null, 'ID de evento inválido.');
}

$stmt = $pdo->prepare("
    SELECT e.*,
           e.capacidade - COALESCE((
               SELECT SUM(ic.quantidade)
               FROM itens_compra ic
               JOIN compras c ON c.id = ic.compra_id
               WHERE c.evento_id = e.id AND c.estado = 'confirmado'
           ), 0) AS lugares_disponiveis
    FROM eventos e
    WHERE e.id = ? AND e.estado != 'cancelado'
");
$stmt->execute([$id]);
$evento = $stmt->fetch();

if (!$evento) {
    json_resposta(false, null, 'Evento não encontrado.');
}

// Preços
$stmtP = $pdo->prepare("SELECT tipo, preco FROM precos WHERE evento_id = ?");
$stmtP->execute([$id]);
$evento['precos'] = $stmtP->fetchAll();

json_resposta(true, $evento);
