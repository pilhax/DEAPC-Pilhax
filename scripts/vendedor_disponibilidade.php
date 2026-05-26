<?php
/**
 * vendedor_disponibilidade.php — Disponibilidade em tempo real (VND02)
 * GET: evento_id?
 * Devolve JSON com lista de eventos e lugares disponíveis.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

iniciar_sessao();
$u = verificar_sessao();
if (!in_array($u['perfil'], ['vendedor', 'administrador'], true)) {
    json_resposta(false, null, 'Acesso negado.');
}

$pdo       = obter_ligacao();
$evento_id = (int) ($_GET['evento_id'] ?? 0);

$where  = "e.estado = 'publicado'";
$params = [];
if ($evento_id > 0) {
    $where   .= " AND e.id = ?";
    $params[] = $evento_id;
}

$stmt = $pdo->prepare("
    SELECT e.id, e.nome, e.data, e.hora, e.sala, e.capacidade,
           COALESCE((
               SELECT SUM(ic.quantidade)
               FROM itens_compra ic JOIN compras c ON c.id = ic.compra_id
               WHERE c.evento_id = e.id AND c.estado = 'confirmado'
           ), 0) AS vendidos,
           e.capacidade - COALESCE((
               SELECT SUM(ic.quantidade)
               FROM itens_compra ic JOIN compras c ON c.id = ic.compra_id
               WHERE c.evento_id = e.id AND c.estado = 'confirmado'
           ), 0) AS disponiveis,
           MIN(CASE WHEN p.tipo='normal' THEN p.preco END) AS preco_normal,
           MIN(CASE WHEN p.tipo='jovem'  THEN p.preco END) AS preco_jovem,
           MIN(CASE WHEN p.tipo='senior' THEN p.preco END) AS preco_senior
    FROM eventos e
    LEFT JOIN precos p ON p.evento_id = e.id
    WHERE $where
    GROUP BY e.id
    ORDER BY e.data ASC
");
$stmt->execute($params);

json_resposta(true, $stmt->fetchAll());
