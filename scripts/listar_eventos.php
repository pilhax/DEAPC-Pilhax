<?php
/**
 * listar_eventos.php — Lista eventos publicados com preços (CLI01, CLI02)
 * GET: pesquisa?, categoria?, data?
 * Devolve JSON.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

inicializar_bd();
$pdo = obter_ligacao();

$pesquisa  = trim($_GET['pesquisa']  ?? '');
$categoria = trim($_GET['categoria'] ?? '');
$data      = trim($_GET['data']      ?? '');

$where  = ["e.estado = 'publicado'", "e.data >= date('now')"];
$params = [];

if ($pesquisa !== '') {
    $where[]  = "(e.nome LIKE ? OR e.descricao LIKE ?)";
    $params[] = "%$pesquisa%";
    $params[] = "%$pesquisa%";
}
if ($categoria !== '') {
    $where[]  = "e.categoria = ?";
    $params[] = $categoria;
}
if ($data !== '') {
    $where[]  = "e.data = ?";
    $params[] = $data;
}

$sql = "
    SELECT e.*,
           MIN(CASE WHEN p.tipo='normal' THEN p.preco END) AS preco_normal,
           MIN(CASE WHEN p.tipo='jovem'  THEN p.preco END) AS preco_jovem,
           MIN(CASE WHEN p.tipo='senior' THEN p.preco END) AS preco_senior,
           e.capacidade - COALESCE((
               SELECT SUM(ic.quantidade)
               FROM itens_compra ic
               JOIN compras c ON c.id = ic.compra_id
               WHERE c.evento_id = e.id AND c.estado = 'confirmado'
           ), 0) AS lugares_disponiveis
    FROM eventos e
    LEFT JOIN precos p ON p.evento_id = e.id
    WHERE " . implode(' AND ', $where) . "
    GROUP BY e.id
    ORDER BY e.data ASC, e.hora ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$eventos = $stmt->fetchAll();

json_resposta(true, $eventos);
