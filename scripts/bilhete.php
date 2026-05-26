<?php
/**
 * bilhete.php — Devolve detalhe de uma compra por referência (CLI06, CLI08)
 * GET: ref
 * Devolve JSON.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

inicializar_bd();
$pdo = obter_ligacao();

$ref = trim($_GET['ref'] ?? '');
if ($ref === '') {
    json_resposta(false, null, 'Referência não indicada.');
}

$stmt = $pdo->prepare("
    SELECT c.*, e.nome AS evento_nome, e.data AS evento_data, e.hora AS evento_hora,
           e.sala AS evento_sala, e.categoria AS evento_categoria
    FROM compras c
    JOIN eventos e ON e.id = c.evento_id
    WHERE c.referencia = ?
");
$stmt->execute([$ref]);
$compra = $stmt->fetch();

if (!$compra) {
    json_resposta(false, null, 'Compra não encontrada.');
}

// Itens
$stmtI = $pdo->prepare("SELECT tipo, quantidade, preco_unitario FROM itens_compra WHERE compra_id = ?");
$stmtI->execute([$compra['id']]);
$compra['itens'] = $stmtI->fetchAll();

json_resposta(true, $compra);
