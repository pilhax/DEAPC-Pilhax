<?php
/**
 * Inicializa a base de dados e insere dados de exemplo.
 * Executar uma vez: php scripts/setup.php
 * Ou aceder via browser (apenas em ambiente local).
 */

require_once __DIR__ . '/db.php';

inicializar_bd();
$pdo = obter_ligacao();

// ── Utilizadores de exemplo ────────────────────────────────────────────────
$utilizadores = [
    ['Paulo',  'Silva',    'paulo@cdmusica.pt',  password_hash('admin123',   PASSWORD_DEFAULT), 'administrador', null,         null,        null],
    ['João',   'Rodrigues','joao@cdmusica.pt',   password_hash('vendedor123',PASSWORD_DEFAULT), 'vendedor',      null,         null,        null],
    ['Ana',    'Ferreira', 'ana@exemplo.com',    password_hash('cliente123', PASSWORD_DEFAULT), 'cliente',       '1998-03-14', '236781920', '+351912345678'],
    ['Carlos', 'Mota',     'carlos@gmail.com',   password_hash('cliente123', PASSWORD_DEFAULT), 'cliente',       '1980-07-22', null,        null],
];

$stmt = $pdo->prepare("
    INSERT OR IGNORE INTO utilizadores (nome, apelido, email, password_hash, perfil, data_nascimento, nif, telefone)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
foreach ($utilizadores as $u) {
    $stmt->execute($u);
}

// ── Eventos de exemplo ─────────────────────────────────────────────────────
$eventos = [
    ['Orquestra Sinfónica do Porto',  'Uma noite de sublime música clássica com a OSP.', '2026-06-07', '21:00', 'Sala Suggia',      'Música Clássica',     'Maiores de 6 anos', 340, 'publicado'],
    ['Noite de Jazz com Maria João',  'Jazz português com Maria João.',                  '2026-06-13', '22:00', 'Grande Auditório', 'Jazz',                'Livre',             320, 'publicado'],
    ['La Traviata — Verdi',           'Ópera em três atos de Giuseppe Verdi.',           '2026-06-15', '19:30', 'Sala Suggia',      'Ópera',               'Maiores de 6 anos', 340, 'publicado'],
    ['Quarteto de Cordas Nº 14',      'Schubert interpretado pelo Quarteto Nacional.',   '2026-06-19', '20:00', 'Sala 2',           'Música de Câmara',    'Livre',             120, 'publicado'],
    ['Festival de Música Nova',       'Estreias absolutas de compositores portugueses.', '2026-06-21', '21:30', 'Grande Auditório', 'Música Contemporânea','Livre',             320, 'publicado'],
    ['Fado e Guitarra Portuguesa',    'Uma noite de fado com os melhores intérpretes.', '2026-06-27', '21:00', 'Sala Suggia',      'World Music',         'Livre',             340, 'publicado'],
];

$stmtE = $pdo->prepare("
    INSERT OR IGNORE INTO eventos (nome, descricao, data, hora, sala, categoria, classificacao_etaria, capacidade, estado)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmtP = $pdo->prepare("
    INSERT OR IGNORE INTO precos (evento_id, tipo, preco) VALUES (?, ?, ?)
");

$precos_por_evento = [
    [20.0, 12.0, 14.0],
    [15.0, 10.0, 12.0],
    [25.0, 18.0, 20.0],
    [10.0,  8.0,  8.0],
    [ 8.0,  6.0,  6.0],
    [14.0, 10.0, 12.0],
];

foreach ($eventos as $i => $ev) {
    $stmtE->execute($ev);
    $id = (int) $pdo->lastInsertId();
    if ($id > 0) {
        [$pn, $pj, $ps] = $precos_por_evento[$i];
        $stmtP->execute([$id, 'normal', $pn]);
        $stmtP->execute([$id, 'jovem',  $pj]);
        $stmtP->execute([$id, 'senior', $ps]);
    }
}

// ── Compras de exemplo ─────────────────────────────────────────────────────
$existente = $pdo->query("SELECT COUNT(*) FROM compras")->fetchColumn();
if ((int) $existente === 0) {
    $pdo->exec("
        INSERT INTO compras (referencia, evento_id, utilizador_id, nome_cliente, email_cliente,
                             telefone_cliente, canal, metodo_pagamento, total, data_compra)
        VALUES
        ('CDM-2026-08471', 1, 3, 'Ana Ferreira', 'ana@exemplo.com',
         '+351912345678', 'online', 'cartao', 40.00, '2026-05-25 18:30:00'),
        ('CDM-2026-04221', 2, 3, 'Ana Ferreira', 'ana@exemplo.com',
         '+351912345678', 'online', 'mbway',  12.00, '2026-03-14 19:00:00'),
        ('CDM-2026-01108', 6, 3, 'Ana Ferreira', 'ana@exemplo.com',
         '+351912345678', 'online', 'cartao', 42.00, '2026-01-18 15:00:00');
    ");
    $pdo->exec("
        INSERT INTO itens_compra (compra_id, tipo, quantidade, preco_unitario) VALUES
        (1, 'normal', 2, 20.00),
        (2, 'jovem',  1, 12.00),
        (3, 'normal', 3, 14.00);
    ");
}

echo "Base de dados inicializada com sucesso.\n";
echo "Utilizadores criados: " . count($utilizadores) . "\n";
echo "Eventos criados: " . count($eventos) . "\n";
