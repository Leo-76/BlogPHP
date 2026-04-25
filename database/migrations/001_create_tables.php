<?php
use Doctrine\DBAL\Connection;

return new class {
    public function up(Connection $conn): void
    {
        $conn->executeStatement("
            CREATE TABLE IF NOT EXISTS categories (
                id   INTEGER PRIMARY KEY AUTOINCREMENT,
                nom  TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE
            )");
        $conn->executeStatement("
            CREATE TABLE IF NOT EXISTS articles (
                id           INTEGER PRIMARY KEY AUTOINCREMENT,
                titre        TEXT NOT NULL,
                slug         TEXT NOT NULL UNIQUE,
                extrait      TEXT,
                contenu      TEXT NOT NULL,
                image        TEXT,
                categorie_id INTEGER REFERENCES categories(id),
                publie       INTEGER DEFAULT 1,
                created_at   TEXT DEFAULT (datetime('now')),
                updated_at   TEXT DEFAULT (datetime('now'))
            )");
        $conn->executeStatement("
            CREATE TABLE IF NOT EXISTS commentaires (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                article_id INTEGER REFERENCES articles(id) ON DELETE CASCADE,
                auteur     TEXT NOT NULL,
                email      TEXT NOT NULL,
                contenu    TEXT NOT NULL,
                approuve   INTEGER DEFAULT 0,
                created_at TEXT DEFAULT (datetime('now'))
            )");
        $conn->executeStatement("
            CREATE TABLE IF NOT EXISTS users (
                id                 INTEGER PRIMARY KEY AUTOINCREMENT,
                nom                TEXT NOT NULL,
                email              TEXT NOT NULL UNIQUE,
                password           TEXT NOT NULL,
                role               TEXT DEFAULT 'user',
                actif              INTEGER DEFAULT 1,
                avatar             TEXT,
                api_token          TEXT UNIQUE,
                reset_token        TEXT,
                reset_expires      TEXT,
                token_verification TEXT,
                created_at         TEXT DEFAULT (datetime('now')),
                updated_at         TEXT DEFAULT (datetime('now'))
            )");
    }

    public function down(Connection $conn): void
    {
        foreach (['commentaires','articles','categories','users'] as $t) {
            $conn->executeStatement("DROP TABLE IF EXISTS {$t}");
        }
    }
};
