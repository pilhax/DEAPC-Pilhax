<?php
/**
 * comprar_bilhete.php — Processa compra online (CLI04, CLI05, CLI06)
 * POST: evento_id, nome, email, telefone, nif, pagamento,
 *       qty_normal, qty_jovem, qty_senior, csrf_token
 * Redireciona para confirmacao.html?ref=XXX
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

inicializar_bd();
iniciar_sessao();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.html');
    exit;
}

verificar_csrf();

$evento_id   = (int)   ($_POST['evento_id']  ?? 0);
$nome        = trim($_POST['nome']           ?? '');
$email       = trim($_POST['email']          ?? '');
$telefone    = trim($_POST['telefone']       ?? '');
$nif         = trim($_POST['nif']            ?? '');
$pagamento   = trim($_POST['pagamento']      ?? 'cartao');
$qty_normal  = max(0, (int) ($_POST['qty_normal'] ?? 0));
$qty_jovem   = max(0, (int) ($_POST['qty_jovem']  ?? 0));
$qty_senior  = max(0, (int) ($_POST['qty_senior'] ?? 0));

$total_bilhetes = $qty_normal + $qty_jovem + $qty_senior;

if ($evento_id <= 0 || $nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $total_bilhetes === 0) {
    redirecionar_erro('../checkout.html', 'Dados inválidos. Verifique o formulário.');
}

try {
    $pdo = obter_ligacao();

    // Verificar evento e disponibilidade
    $ev = $pdo->prepare("
        SELECT e.*, e.capacidade - COALESCE((
            SELECT SUM(ic.quantidade)
            FROM itens_compra ic JOIN compras c ON c.id = ic.compra_id
            WHERE c.evento_id = e.id AND c.estado = 'confirmado'
        ), 0) AS lugares_disponiveis
        FROM eventos e WHERE e.id = ? AND e.estado = 'publicado'
    ");
    $ev->execute([$evento_id]);
    $evento = $ev->fetch();

    if (!$evento) {
        redirecionar_erro('../checkout.html', 'Evento não disponível.');
    }
    if ((int) $evento['lugares_disponiveis'] < $total_bilhetes) {
        redirecionar_erro('../checkout.html', 'Não há lugares suficientes disponíveis.');
    }

    // Calcular total
    $precos = $pdo->prepare("SELECT tipo, preco FROM precos WHERE evento_id = ?");
    $precos->execute([$evento_id]);
    $mapa = array_column($precos->fetchAll(), 'preco', 'tipo');

    $total = ($qty_normal * ($mapa['normal'] ?? 0))
           + ($qty_jovem  * ($mapa['jovem']  ?? 0))
           + ($qty_senior * ($mapa['senior'] ?? 0));

    // Referência única
    $referencia = 'CDM-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

    $utilizador_id = sessao_utilizador()['id'] ?? null;

    $pdo->beginTransaction();

    $pdo->prepare("
        INSERT INTO compras (referencia, evento_id, utilizador_id, nome_cliente, email_cliente,
                             telefone_cliente, nif_cliente, canal, metodo_pagamento, total)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'online', ?, ?)
    ")->execute([$referencia, $evento_id, $utilizador_id, $nome, $email, $telefone ?: null, $nif ?: null, $pagamento, $total]);

    $compra_id = (int) $pdo->lastInsertId();

    $stmtI = $pdo->prepare("
        INSERT INTO itens_compra (compra_id, tipo, quantidade, preco_unitario) VALUES (?, ?, ?, ?)
    ");
    if ($qty_normal > 0) $stmtI->execute([$compra_id, 'normal', $qty_normal, $mapa['normal']]);
    if ($qty_jovem  > 0) $stmtI->execute([$compra_id, 'jovem',  $qty_jovem,  $mapa['jovem']]);
    if ($qty_senior > 0) $stmtI->execute([$compra_id, 'senior', $qty_senior, $mapa['senior']]);

    $pdo->commit();

    header('Location: ../confirmacao.html?ref=' . urlencode($referencia));
    exit;

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    redirecionar_erro('../checkout.html', 'Erro ao processar pagamento. Tente novamente.');
}
