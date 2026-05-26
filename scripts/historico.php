<?php
/**
 * historico.php — Histórico de compras do utilizador autenticado (CLI08)
 * GET: estado? (todos|futuros|passados)
 * Devolve JSON.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

inicializar_bd();
$utilizador = verificar_sessao();

$pdo   = obter_ligacao();
$estado = trim($_GET['estado'] ?? 'todos');

$where = "c.utilizador_id = ? AND c.estado = 'confirmado'";
$params = [$utilizador['id']];

if ($estado === 'futuros') {
    $where .= " AND e.data >= date('now')";
} elseif ($estado === 'passados') {
    $where .= " AND e.data < date('now')";
}

$stmt = $pdo->prepare("
    SELECT c.referencia, c.total, c.data_compra, c.metodo_pagamento, c.canal,
           e.nome AS evento_nome, e.data AS evento_data, e.hora AS evento_hora,
           e.sala AS evento_sala, e.categoria AS evento_categoria,
           (SELECT GROUP_CONCAT(ic.tipo || ' × ' || ic.quantidade)
            FROM itens_compra ic WHERE ic.compra_id = c.id) AS resumo_bilhetes
    FROM compras c
    JOIN eventos e ON e.id = c.evento_id
    WHERE $where
    ORDER BY e.data DESC
");
$stmt->execute($params);
$compras = $stmt->fetchAll();

json_resposta(true, $compras);
