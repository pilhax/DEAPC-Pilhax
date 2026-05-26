<?php
/**
 * registos_acesso.php — Consulta de registos de acesso (Q8b, Q8c)
 * GET: utilizador_id? (omitir = todos), limite? (default 100)
 * Requer perfil administrador.
 * Devolve JSON.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

iniciar_sessao();
verificar_permissao('administrador');

$pdo           = obter_ligacao();
$utilizador_id = (int) ($_GET['utilizador_id'] ?? 0);
$limite        = min(500, max(1, (int) ($_GET['limite'] ?? 100)));

$where  = '1=1';
$params = [];

if ($utilizador_id > 0) {
    $where   .= ' AND a.utilizador_id = ?';
    $params[] = $utilizador_id;
}

$stmt = $pdo->prepare("
    SELECT a.id, a.data_hora, a.ip,
           u.id AS utilizador_id,
           u.nome || ' ' || u.apelido AS nome_completo,
           u.email, u.perfil
    FROM acessos a
    JOIN utilizadores u ON u.id = a.utilizador_id
    WHERE $where
    ORDER BY a.data_hora DESC
    LIMIT $limite
");
$stmt->execute($params);
$acessos = $stmt->fetchAll();

// Resumo por utilizador
$resumo = $pdo->query("
    SELECT u.id, u.nome || ' ' || u.apelido AS nome, u.email, u.perfil,
           COUNT(a.id) AS total_acessos,
           MAX(a.data_hora) AS ultimo_acesso
    FROM utilizadores u
    LEFT JOIN acessos a ON a.utilizador_id = u.id
    GROUP BY u.id
    ORDER BY total_acessos DESC
")->fetchAll();

json_resposta(true, ['acessos' => $acessos, 'resumo_por_utilizador' => $resumo]);
