<?php
/**
 * Inicializa a base de dados e insere dados reais da Casa da Música 2026.
 * Executar: php scripts/setup.php
 *
 * Fontes: casadamusica.com, agendaculturalporto.org, viralagenda.com (Mai–Out 2026)
 */

require_once __DIR__ . '/db.php';

inicializar_bd();
$pdo = obter_ligacao();

// ── Utilizadores ──────────────────────────────────────────────────────────
$utilizadores = [
    ['Paulo',  'Silva',     'paulo@cdmusica.pt',  password_hash('admin123',    PASSWORD_DEFAULT), 'administrador', null,         null,        null],
    ['João',   'Rodrigues', 'joao@cdmusica.pt',   password_hash('vendedor123', PASSWORD_DEFAULT), 'vendedor',      null,         null,        null],
    ['Ana',    'Ferreira',  'ana@exemplo.com',    password_hash('cliente123',  PASSWORD_DEFAULT), 'cliente',       '1998-03-14', '236781920', '+351912345678'],
    ['Carlos', 'Mota',      'carlos@gmail.com',   password_hash('cliente123',  PASSWORD_DEFAULT), 'cliente',       '1980-07-22', null,        null],
];

$stmtU = $pdo->prepare("
    INSERT OR IGNORE INTO utilizadores (nome, apelido, email, password_hash, perfil, data_nascimento, nif, telefone)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
foreach ($utilizadores as $u) {
    $stmtU->execute($u);
}

// ── Eventos reais 2026 ────────────────────────────────────────────────────
// [nome, descricao, data, hora, sala, categoria, classificacao_etaria, capacidade, estado]
$eventos = [
    [
        'In Dia Duo Guitar',
        'Concerto de guitarra a dois com o duo In Dia Duo, num programa dedicado ao universo da guitarra clássica.',
        '2026-05-26', '21:00', 'Sala 2', 'Música de Câmara', 'Livre', 300, 'publicado',
    ],
    [
        'Francisco Rua ao vivo',
        'O guitarrista Francisco Rua apresenta o seu mais recente projeto no Bar da Casa da Música.',
        '2026-05-28', '21:30', 'Sala 2', 'Jazz', 'Livre', 300, 'publicado',
    ],
    [
        '3 Concertos para Guitarra',
        'Sean Shibe (ECHO Rising Star) e Alice Brandão em três concertos para guitarra e orquestra, com a Orquestra Sinfónica do Porto Casa da Música.',
        '2026-05-29', '21:00', 'Sala Suggia', 'Música Clássica', 'Maiores de 6 anos', 1238, 'publicado',
    ],
    [
        'Maratona de Guitarristas',
        'Uma maratona de performances de guitarra com dezenas de intérpretes ao longo do dia, de estilos variados.',
        '2026-05-30', '10:00', 'Sala Suggia', 'Música Clássica', 'Livre', 1238, 'publicado',
    ],
    [
        'Orquestra Portuguesa de Guitarras e Bandolins com Yamandú Costa',
        'A OPGB recebe o lendário guitarrista brasileiro Yamandú Costa num encontro entre tradição e inovação.',
        '2026-05-30', '18:00', 'Sala Suggia', 'World Music', 'Livre', 1238, 'publicado',
    ],
    [
        'Bombino',
        'O primeiro artista do Níger nomeado para um Grammy apresenta o álbum Sahel — guitarras do deserto e ritmos tuaregues.',
        '2026-05-31', '21:00', 'Sala Suggia', 'World Music', 'Livre', 1238, 'publicado',
    ],
    [
        'Conservatório de Música do Porto — Concerto de Fim de Ano',
        'Concerto anual de encerramento do ano letivo do Conservatório de Música do Porto com alunos de todos os níveis.',
        '2026-06-02', '21:00', 'Sala Suggia', 'Música Clássica', 'Livre', 1238, 'publicado',
    ],
    [
        'Farinelli',
        'A Orquestra Barroca da Casa da Música, sob direção artística de Huw Daniel, acompanha ao vivo a projeção do filme Farinelli (1994) com a sua extraordinária banda sonora original.',
        '2026-06-07', '18:00', 'Sala Suggia', 'Ópera', 'Maiores de 6 anos', 1238, 'publicado',
    ],
    [
        'Teoria das Cordas',
        'Concerto de música contemporânea dedicado às cordas, com obras de compositores do século XXI.',
        '2026-06-07', '21:00', 'Sala Suggia', 'Música Contemporânea', 'Livre', 1238, 'publicado',
    ],
    [
        'Sing Together!',
        'Concerto coral participativo que convida o público a cantar junto com os coros residentes da Casa da Música.',
        '2026-06-10', '19:00', 'Sala Suggia', 'Música Coral', 'Livre', 1238, 'publicado',
    ],
    [
        'Quarteto de Cordas de Matosinhos',
        'O Quarteto de Cordas de Matosinhos apresenta um programa com obras de Haydn, Schumann e Ravel.',
        '2026-06-11', '19:30', 'Sala 2', 'Música de Câmara', 'Livre', 300, 'publicado',
    ],
    [
        'Vasco Mendonça — Third Places',
        'Estreia absoluta de nova obra do compositor português Vasco Mendonça, encomendada pela Casa da Música.',
        '2026-06-13', '16:00', 'Sala Suggia', 'Música Contemporânea', 'Livre', 1238, 'publicado',
    ],
    [
        'Coral Sinfónico',
        'O Coro Casa da Música e a Orquestra Sinfónica do Porto apresentam um programa coral sinfónico com obras de Brahms e Verdi.',
        '2026-06-13', '18:00', 'Sala Suggia', 'Música Coral', 'Livre', 1238, 'publicado',
    ],
    [
        'ANAVITÓRIA VOZ CLARABÓIA',
        'O duo brasileiro ANAVITÓRIA apresenta o seu sexto álbum de estúdio, Claraboia, num concerto íntimo de voz e guitarra.',
        '2026-06-16', '21:00', 'Sala Suggia', 'Música Popular', 'Livre', 1238, 'publicado',
    ],
    [
        'TT Syndicate — Verão da Casa (Terraço)',
        'Concerto gratuito na esplanada da Casa da Música, integrado no programa Verão da Casa.',
        '2026-06-19', '21:30', 'Terraço', 'Música Popular', 'Livre', 500, 'publicado',
    ],
    [
        'São João — Tio Jel + Ena Pá 2000',
        'Noite de São João no Cais de Carga com Tio Jel, Ena Pá 2000 e DJ Fernando Alvim. Entrada livre.',
        '2026-06-23', '22:00', 'Cais de Carga', 'Música Popular', 'Livre', 2000, 'publicado',
    ],
    [
        'OSP — Concerto de Verão',
        'A Orquestra Sinfónica do Porto apresenta o seu concerto de verão com um programa dedicado ao repertório romântico.',
        '2026-06-27', '18:00', 'Sala Suggia', 'Música Clássica', 'Livre', 1238, 'publicado',
    ],
    [
        'Prémio Internacional Suggia',
        'Os três finalistas do Prémio Internacional Suggia interpretam concertos para violoncelo e orquestra no palco da Sala Suggia, com a Orquestra Sinfónica do Porto dirigida por Jan Wierzba.',
        '2026-07-03', '21:00', 'Sala Suggia', 'Música Clássica', 'Livre', 1238, 'publicado',
    ],
    [
        'Orff Orchestra of Porto — 40 Anos',
        'A Orff Orchestra of Porto celebra 40 anos de atividade num concerto especial de aniversário.',
        '2026-07-04', '18:00', 'Sala 2', 'Música Clássica', 'Livre', 300, 'publicado',
    ],
    [
        'Enio — Verão da Casa (Terraço)',
        'Concerto gratuito na esplanada da Casa da Música com Enio, integrado no programa Verão da Casa.',
        '2026-07-12', '21:30', 'Terraço', 'Música Popular', 'Livre', 500, 'publicado',
    ],
    [
        'Patche di Rima & Poeta Lit G',
        'Encontro de dois dos nomes mais relevantes do hip-hop e spoken word português num concerto único.',
        '2026-07-18', '21:00', 'Sala Suggia', 'Hip-Hop', 'Maiores de 12 anos', 1238, 'publicado',
    ],
    [
        'João Rosa Quinteto',
        'O saxofonista João Rosa apresenta o seu quinteto num concerto de jazz modal e improvisação livre.',
        '2026-07-25', '21:00', 'Sala 2', 'Jazz', 'Livre', 300, 'publicado',
    ],
    [
        'Trinka — Verão da Casa (Terraço)',
        'Concerto gratuito na esplanada da Casa da Música com Trinka, integrado no programa Verão da Casa.',
        '2026-08-01', '21:30', 'Terraço', 'Música Popular', 'Livre', 500, 'publicado',
    ],
    [
        'Ricardo Dias Gomes — Verão da Casa (Terraço)',
        'Concerto gratuito na esplanada da Casa da Música com Ricardo Dias Gomes.',
        '2026-08-15', '21:30', 'Terraço', 'Música Popular', 'Livre', 500, 'publicado',
    ],
    [
        'Banda Sinfónica Portuguesa',
        'A Banda Sinfónica Portuguesa apresenta um programa dedicado ao repertório para banda do século XX.',
        '2026-08-22', '18:00', 'Sala Suggia', 'Música Clássica', 'Livre', 1238, 'publicado',
    ],
    [
        'Floresta do Amazonas — Villa-Lobos',
        'A Orquestra Sinfónica do Porto interpreta Floresta do Amazonas de Villa-Lobos com cenografia imersiva da artista visual Bianca Dacosta.',
        '2026-09-18', '21:00', 'Sala Suggia', 'Música Clássica', 'Livre', 1238, 'publicado',
    ],
    [
        'Remix Ensemble & Orquestra Barroca',
        'O Remix Ensemble e a Orquestra Barroca partilham palco em dois programas distintos com obras de Mauro Hertig Parra.',
        '2026-09-25', '21:00', 'Sala Suggia', 'Música Contemporânea', 'Livre', 1238, 'publicado',
    ],
    [
        'Joe Jackson + Band',
        'O incontornável Joe Jackson traz o seu Hope and Fury Tour 2026 ao Porto, apresentando o novo álbum que funde ritmos latinos, jazz, funk e rock.',
        '2026-10-17', '21:00', 'Sala Suggia', 'Rock', 'Livre', 1238, 'publicado',
    ],
    [
        'OSP — Tchaikovsky: O Quebra-Nozes',
        'A Orquestra Sinfónica do Porto e o Coro de Crianças apresentam O Quebra-Nozes de Tchaikovsky com ilustrações de areia ao vivo.',
        '2026-11-28', '16:00', 'Sala Suggia', 'Música Clássica', 'Maiores de 3 anos', 1238, 'publicado',
    ],
    [
        'Concerto de Ano Novo 2027',
        'A Orquestra Sinfónica do Porto inaugura o novo ano com um programa dedicado à música vienense.',
        '2027-01-03', '17:00', 'Sala Suggia', 'Música Clássica', 'Livre', 1238, 'publicado',
    ],
];

// Preços por evento: [normal, jovem, senior]
// Terraço/São João: entrada livre (0)
$precos = [
    [12.00,  6.00, 10.00],  // In Dia Duo
    [10.00,  5.00,  8.00],  // Francisco Rua
    [34.00, 17.00, 27.00],  // 3 Concertos Guitarra
    [ 3.00,  3.00,  3.00],  // Maratona Guitarristas
    [15.00,  8.00, 12.00],  // OPGB + Yamandú
    [30.00, 15.00, 24.00],  // Bombino
    [10.00,  5.00,  8.00],  // Conservatório
    [34.00, 17.00, 27.00],  // Farinelli
    [22.00, 11.00, 18.00],  // Teoria das Cordas
    [12.00,  6.00, 10.00],  // Sing Together
    [12.00,  6.00, 10.00],  // Quarteto Matosinhos
    [18.00,  9.00, 14.00],  // Vasco Mendonça
    [16.00,  8.00, 13.00],  // Coral Sinfónico
    [30.00, 15.00, 24.00],  // ANAVITÓRIA
    [ 0.00,  0.00,  0.00],  // TT Syndicate (livre)
    [ 0.00,  0.00,  0.00],  // São João (livre)
    [26.00, 13.00, 21.00],  // OSP Verão
    [26.00, 13.00, 21.00],  // Prémio Suggia
    [10.00,  5.00,  8.00],  // Orff Orchestra
    [ 0.00,  0.00,  0.00],  // Enio (livre)
    [18.00,  9.00, 14.00],  // Patche di Rima
    [14.00,  7.00, 11.00],  // João Rosa Quinteto
    [ 0.00,  0.00,  0.00],  // Trinka (livre)
    [ 0.00,  0.00,  0.00],  // Ricardo Dias Gomes (livre)
    [14.00,  7.00, 11.00],  // Banda Sinfónica
    [26.00, 13.00, 21.00],  // Floresta Amazonas
    [22.00, 11.00, 18.00],  // Remix Ensemble
    [35.00, 18.00, 28.00],  // Joe Jackson
    [18.00,  9.00, 14.00],  // Quebra-Nozes
    [22.00, 11.00, 18.00],  // Ano Novo 2027
];

$stmtE = $pdo->prepare("
    INSERT OR IGNORE INTO eventos (nome, descricao, data, hora, sala, categoria, classificacao_etaria, capacidade, estado)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmtP = $pdo->prepare("
    INSERT OR IGNORE INTO precos (evento_id, tipo, preco) VALUES (?, ?, ?)
");

foreach ($eventos as $i => $ev) {
    $stmtE->execute($ev);
    $id = (int) $pdo->lastInsertId();
    if ($id > 0) {
        [$pn, $pj, $ps] = $precos[$i];
        $stmtP->execute([$id, 'normal', $pn]);
        $stmtP->execute([$id, 'jovem',  $pj]);
        $stmtP->execute([$id, 'senior', $ps]);
    }
}

// ── Compras de exemplo ─────────────────────────────────────────────────────
$existente = (int) $pdo->query("SELECT COUNT(*) FROM compras")->fetchColumn();
if ($existente === 0) {
    $pdo->exec("
        INSERT INTO compras (referencia, evento_id, utilizador_id, nome_cliente, email_cliente,
                             canal, metodo_pagamento, total, data_compra)
        VALUES
        ('CDM-2026-A1B2C3', 8, 3, 'Ana Ferreira', 'ana@exemplo.com', 'online', 'cartao', 68.00, '2026-05-20 10:00:00'),
        ('CDM-2026-D4E5F6', 6, 3, 'Ana Ferreira', 'ana@exemplo.com', 'online', 'mbway',  30.00, '2026-05-15 14:30:00'),
        ('CDM-2026-G7H8I9', 17, 4, 'Carlos Mota', 'carlos@gmail.com','online', 'cartao', 26.00, '2026-05-22 09:00:00')
    ");
    $pdo->exec("
        INSERT INTO itens_compra (compra_id, tipo, quantidade, preco_unitario) VALUES
        (1, 'normal', 2, 34.00),
        (2, 'normal', 1, 30.00),
        (3, 'normal', 1, 26.00)
    ");
}

$nEventos = (int) $pdo->query("SELECT COUNT(*) FROM eventos")->fetchColumn();
echo "Base de dados inicializada com sucesso.\n";
echo "Eventos inseridos: $nEventos\n";
echo "Utilizadores: " . count($utilizadores) . "\n";
echo "Ficheiro: " . DB_FILE . "\n";
