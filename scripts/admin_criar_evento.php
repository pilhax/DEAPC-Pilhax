<?php
/**
 * admin_criar_evento.php — Criar novo evento (ADM01, ADM04)
 * POST: nome, descricao, data, hora, sala, categoria, classificacao_etaria,
 *       capacidade, preco_normal, preco_jovem, preco_senior, estado, csrf_token
 * Devolve JSON.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

iniciar_sessao();
verificar_permissao('administrador');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_resposta(false, null, 'Método não permitido.');
}

$dados = [
    'nome'                => trim($_POST['nome']                ?? ''),
    'descricao'           => trim($_POST['descricao']           ?? ''),
    'data'                => trim($_POST['data']                ?? ''),
    'hora'                => trim($_POST['hora']                ?? ''),
    'sala'                => trim($_POST['sala']                ?? ''),
    'categoria'           => trim($_POST['categoria']           ?? ''),
    'classificacao_etaria'=> trim($_POST['classificacao_etaria']?? 'Livre'),
    'capacidade'          => (int) ($_POST['capacidade']        ?? 300),
    'estado'              => trim($_POST['estado']              ?? 'rascunho'),
    'preco_normal'        => (float) ($_POST['preco_normal']    ?? 0),
    'preco_jovem'         => (float) ($_POST['preco_jovem']     ?? 0),
    'preco_senior'        => (float) ($_POST['preco_senior']    ?? 0),
];

if ($dados['nome'] === '' || $dados['data'] === '' || $dados['hora'] === '') {
    json_resposta(false, null, 'Nome, data e hora são obrigatórios.');
}

try {
    $pdo = obter_ligacao();
    $pdo->beginTransaction();

    $pdo->prepare("
        INSERT INTO eventos (nome, descricao, data, hora, sala, categoria,
                             classificacao_etaria, capacidade, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ")->execute([
        $dados['nome'], $dados['descricao'], $dados['data'], $dados['hora'],
        $dados['sala'], $dados['categoria'], $dados['classificacao_etaria'],
        $dados['capacidade'], $dados['estado'],
    ]);

    $evento_id = (int) $pdo->lastInsertId();

    $stmtP = $pdo->prepare("INSERT INTO precos (evento_id, tipo, preco) VALUES (?, ?, ?)");
    $stmtP->execute([$evento_id, 'normal', $dados['preco_normal']]);
    $stmtP->execute([$evento_id, 'jovem',  $dados['preco_jovem']]);
    $stmtP->execute([$evento_id, 'senior', $dados['preco_senior']]);

    $pdo->commit();
    json_resposta(true, ['id' => $evento_id]);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    json_resposta(false, null, 'Erro ao criar evento.');
}
