<?php
/**
 * vendedor_vender.php — Venda presencial no balcão (VND01, VND03, VND04)
 * POST: evento_id, qty_normal, qty_jovem, qty_senior,
 *       nome_cliente, email_cliente, metodo_pagamento, csrf_token
 * Devolve JSON com referência e totais.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

iniciar_sessao();
$vendedor = verificar_sessao();

// Vendedor ou administrador podem usar o balcão
if (!in_array($vendedor['perfil'], ['vendedor', 'administrador'], true)) {
    json_resposta(false, null, 'Acesso negado.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_resposta(false, null, 'Método não permitido.');
}

$evento_id      = (int)   ($_POST['evento_id']       ?? 0);
$qty_normal     = max(0, (int)   ($_POST['qty_normal']  ?? 0));
$qty_jovem      = max(0, (int)   ($_POST['qty_jovem']   ?? 0));
$qty_senior     = max(0, (int)   ($_POST['qty_senior']  ?? 0));
$nome_cliente   = trim($_POST['nome_cliente']          ?? 'Cliente Balcão');
$email_cliente  = trim($_POST['email_cliente']         ?? '');
$metodo         = trim($_POST['metodo_pagamento']      ?? 'dinheiro');

$total_bilhetes = $qty_normal + $qty_jovem + $qty_senior;
if ($evento_id <= 0 || $total_bilhetes === 0) {
    json_resposta(false, null, 'Selecione um evento e pelo menos um bilhete.');
}

try {
    $pdo = obter_ligacao();

    // Verificar disponibilidade
    $ev = $pdo->prepare("
        SELECT e.*, e.capacidade - COALESCE((
            SELECT SUM(ic.quantidade) FROM itens_compra ic
            JOIN compras c ON c.id = ic.compra_id
            WHERE c.evento_id = e.id AND c.estado = 'confirmado'
        ), 0) AS lugares_disponiveis
        FROM eventos e WHERE e.id = ? AND e.estado = 'publicado'
    ");
    $ev->execute([$evento_id]);
    $evento = $ev->fetch();

    if (!$evento) {
        json_resposta(false, null, 'Evento não disponível.');
    }
    if ((int) $evento['lugares_disponiveis'] < $total_bilhetes) {
        json_resposta(false, null, 'Lugares insuficientes: ' . $evento['lugares_disponiveis'] . ' disponíveis.');
    }

    $precos = $pdo->prepare("SELECT tipo, preco FROM precos WHERE evento_id = ?");
    $precos->execute([$evento_id]);
    $mapa = array_column($precos->fetchAll(), 'preco', 'tipo');

    $total = ($qty_normal * ($mapa['normal'] ?? 0))
           + ($qty_jovem  * ($mapa['jovem']  ?? 0))
           + ($qty_senior * ($mapa['senior'] ?? 0));

    $referencia = 'CDM-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

    $pdo->beginTransaction();

    $pdo->prepare("
        INSERT INTO compras (referencia, evento_id, nome_cliente, email_cliente,
                             canal, vendedor_id, metodo_pagamento, total)
        VALUES (?, ?, ?, ?, 'presencial', ?, ?, ?)
    ")->execute([$referencia, $evento_id, $nome_cliente,
                 $email_cliente ?: 'balcao@cdmusica.pt',
                 $vendedor['id'], $metodo, $total]);

    $compra_id = (int) $pdo->lastInsertId();

    $stmtI = $pdo->prepare("INSERT INTO itens_compra (compra_id, tipo, quantidade, preco_unitario) VALUES (?,?,?,?)");
    if ($qty_normal > 0) $stmtI->execute([$compra_id, 'normal', $qty_normal, $mapa['normal']]);
    if ($qty_jovem  > 0) $stmtI->execute([$compra_id, 'jovem',  $qty_jovem,  $mapa['jovem']]);
    if ($qty_senior > 0) $stmtI->execute([$compra_id, 'senior', $qty_senior, $mapa['senior']]);

    $pdo->commit();

    json_resposta(true, [
        'referencia'  => $referencia,
        'total'       => $total,
        'evento_nome' => $evento['nome'],
        'evento_data' => $evento['data'],
        'evento_hora' => $evento['hora'],
    ]);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    json_resposta(false, null, 'Erro ao registar venda.');
}
