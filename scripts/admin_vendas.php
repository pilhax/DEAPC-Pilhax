<?php
/**
 * admin_vendas.php — Relatório de vendas por evento (ADM05)
 * GET: evento_id? (omitir = todos)
 * Devolve JSON.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

iniciar_sessao();
verificar_permissao('administrador');

$pdo       = obter_ligacao();
$evento_id = (int) ($_GET['evento_id'] ?? 0);

// Resumo global
$where  = $evento_id > 0 ? "WHERE c.evento_id = $evento_id AND" : "WHERE";
$where .= " c.estado = 'confirmado'";

$resumo = $pdo->query("
    SELECT
        e.id, e.nome, e.data, e.hora, e.sala, e.categoria,
        e.capacidade,
        COALESCE(SUM(ic.quantidade), 0)   AS total_bilhetes,
        COALESCE(SUM(c.total), 0)         AS receita_total,
        ROUND(COALESCE(SUM(ic.quantidade), 0) * 100.0 / e.capacidade, 1) AS ocupacao_pct,
        SUM(CASE WHEN c.canal = 'online'     THEN ic.quantidade ELSE 0 END) AS bilhetes_online,
        SUM(CASE WHEN c.canal = 'presencial' THEN ic.quantidade ELSE 0 END) AS bilhetes_presencial
    FROM eventos e
    LEFT JOIN compras c       ON c.evento_id = e.id AND c.estado = 'confirmado'
    LEFT JOIN itens_compra ic ON ic.compra_id = c.id
    GROUP BY e.id
    ORDER BY e.data DESC
")->fetchAll();

// Vendas recentes (últimas 50)
$recentes = $pdo->query("
    SELECT c.referencia, c.nome_cliente, c.email_cliente, c.canal,
           c.metodo_pagamento, c.total, c.data_compra, c.estado,
           e.nome AS evento_nome,
           GROUP_CONCAT(ic.tipo || ' × ' || ic.quantidade) AS itens
    FROM compras c
    JOIN eventos e       ON e.id = c.evento_id
    JOIN itens_compra ic ON ic.compra_id = c.id
    GROUP BY c.id
    ORDER BY c.data_compra DESC
    LIMIT 50
")->fetchAll();

json_resposta(true, ['resumo_eventos' => $resumo, 'vendas_recentes' => $recentes]);
