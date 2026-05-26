<?php
/**
 * Ligação à base de dados SQLite e inicialização das tabelas.
 */

define('DB_FILE', dirname(__DIR__) . '/data/casaMusica.db');

function obter_ligacao(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dir = dirname(DB_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $pdo = new PDO('sqlite:' . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON;');
        $pdo->exec('PRAGMA journal_mode = WAL;');
    }
    return $pdo;
}

function inicializar_bd(): void {
    $pdo = obter_ligacao();
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS utilizadores (
            id               INTEGER PRIMARY KEY AUTOINCREMENT,
            nome             TEXT    NOT NULL,
            apelido          TEXT    NOT NULL,
            email            TEXT    UNIQUE NOT NULL,
            password_hash    TEXT    NOT NULL,
            perfil           TEXT    NOT NULL DEFAULT 'cliente'
                                     CHECK(perfil IN ('cliente','vendedor','administrador')),
            data_nascimento  TEXT,
            nif              TEXT,
            telefone         TEXT,
            data_registo     TEXT    NOT NULL DEFAULT (datetime('now')),
            ultimo_acesso    TEXT,
            estado           TEXT    NOT NULL DEFAULT 'ativo'
                                     CHECK(estado IN ('ativo','suspenso'))
        );

        CREATE TABLE IF NOT EXISTS eventos (
            id                  INTEGER PRIMARY KEY AUTOINCREMENT,
            nome                TEXT    NOT NULL,
            descricao           TEXT,
            data                TEXT    NOT NULL,
            hora                TEXT    NOT NULL,
            sala                TEXT    NOT NULL,
            categoria           TEXT    NOT NULL,
            classificacao_etaria TEXT   DEFAULT 'Livre',
            capacidade          INTEGER NOT NULL DEFAULT 300,
            estado              TEXT    NOT NULL DEFAULT 'rascunho'
                                        CHECK(estado IN ('publicado','rascunho','cancelado')),
            data_criacao        TEXT    NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS precos (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            evento_id   INTEGER NOT NULL,
            tipo        TEXT    NOT NULL CHECK(tipo IN ('normal','jovem','senior')),
            preco       REAL    NOT NULL,
            FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
            UNIQUE(evento_id, tipo)
        );

        CREATE TABLE IF NOT EXISTS compras (
            id                INTEGER PRIMARY KEY AUTOINCREMENT,
            referencia        TEXT    UNIQUE NOT NULL,
            evento_id         INTEGER NOT NULL,
            utilizador_id     INTEGER,
            nome_cliente      TEXT    NOT NULL,
            email_cliente     TEXT    NOT NULL,
            telefone_cliente  TEXT,
            nif_cliente       TEXT,
            canal             TEXT    NOT NULL DEFAULT 'online'
                                      CHECK(canal IN ('online','presencial')),
            vendedor_id       INTEGER,
            metodo_pagamento  TEXT    NOT NULL,
            total             REAL    NOT NULL,
            estado            TEXT    NOT NULL DEFAULT 'confirmado'
                                      CHECK(estado IN ('confirmado','cancelado')),
            data_compra       TEXT    NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (evento_id)     REFERENCES eventos(id),
            FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id),
            FOREIGN KEY (vendedor_id)   REFERENCES utilizadores(id)
        );

        CREATE TABLE IF NOT EXISTS itens_compra (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            compra_id       INTEGER NOT NULL,
            tipo            TEXT    NOT NULL CHECK(tipo IN ('normal','jovem','senior')),
            quantidade      INTEGER NOT NULL,
            preco_unitario  REAL    NOT NULL,
            FOREIGN KEY (compra_id) REFERENCES compras(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS acessos (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            utilizador_id   INTEGER NOT NULL,
            data_hora       TEXT    NOT NULL DEFAULT (datetime('now')),
            ip              TEXT,
            FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id)
        );
    ");
}
