<?php
/**
 * admin_editar_evento.php — Editar evento existente (ADM02, ADM04)
 * POST: id, nome, descricao, data, hora, sala, categoria, classificacao_etaria,
 *       capacidade, preco_normal, preco_jovem, preco_senior, estado, csrf_token
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
        UPDATE eventos SET nome=?, descricao=?, data=?, hora=?, sala=?, categoria=?,
                           classificacao_etaria=?, capacidade=?, estado=?
        WHERE id=?
    ")->execute([
        $dados['nome'], $dados['descricao'], $dados['data'], $dados['hora'],
        $dados['sala'], $dados['categoria'], $dados['classificacao_etaria'],
        $dados['capacidade'], $dados['estado'], $id,
    ]);

    $stmtP = $pdo->prepare("INSERT OR REPLACE INTO precos (evento_id, tipo, preco) VALUES (?, ?, ?)");
    $stmtP->execute([$id, 'normal', $dados['preco_normal']]);
    $stmtP->execute([$id, 'jovem',  $dados['preco_jovem']]);
    $stmtP->execute([$id, 'senior', $dados['preco_senior']]);

    $pdo->commit();
    json_resposta(true, ['id' => $id]);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    json_resposta(false, null, 'Erro ao atualizar evento.');
}
