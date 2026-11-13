<?php
// Database connection — SQLite via PDO
define('DB_PATH', __DIR__ . '/../data/salon.db');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dir = dirname(DB_PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        try {
            $pdo = new PDO('sqlite:' . DB_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec('PRAGMA journal_mode=WAL;');
            $pdo->exec('PRAGMA foreign_keys=ON;');
            initDB($pdo);
        } catch (PDOException $e) {
            die('Ralat pangkalan data: ' . $e->getMessage());
        }
    }
    return $pdo;
}

function initDB(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS PELANGGAN (
            idPelanggan   TEXT PRIMARY KEY,
            namaPelanggan TEXT NOT NULL,
            noTel         TEXT NOT NULL,
            kataLaluan    TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS PERKHIDMATAN (
            idPerkhidmatan   TEXT PRIMARY KEY,
            namaPerkhidmatan TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS JANJITEMU (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            idPelanggan   TEXT NOT NULL,
            tarikh        TEXT NOT NULL,
            idPerkhidmatan TEXT NOT NULL,
            masa          TEXT NOT NULL,
            catatan       TEXT DEFAULT '',
            status        TEXT DEFAULT 'Menunggu',
            FOREIGN KEY (idPelanggan)    REFERENCES PELANGGAN(idPelanggan),
            FOREIGN KEY (idPerkhidmatan) REFERENCES PERKHIDMATAN(idPerkhidmatan),
            UNIQUE(tarikh, masa)
        );
    ");

    // Seed services if empty
    $count = $pdo->query("SELECT COUNT(*) FROM PERKHIDMATAN")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("
            INSERT INTO PERKHIDMATAN VALUES ('A001','Gunting Rambut');
            INSERT INTO PERKHIDMATAN VALUES ('A002','Gaya Rambut');
            INSERT INTO PERKHIDMATAN VALUES ('A003','Cuci Rambut');
        ");
    }
}
