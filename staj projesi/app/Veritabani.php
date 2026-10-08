<?php
// ============================================================
// Staj Takip - Veritabanı (PDO)
// ============================================================
declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function init(array $cfg): void
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $cfg['db']['host'],
            $cfg['db']['name'],
            $cfg['db']['charset']
        );
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            self::$pdo = new PDO($dsn, $cfg['db']['user'], $cfg['db']['password'], $opt);
        } catch (PDOException $e) {
            http_response_code(500);
            echo '<!doctype html><meta charset="utf-8"><title>Veritabanı hatası</title>';
            echo '<h1>Veritabanına bağlanılamıyor.</h1>';
            echo '<p>Sunucu yöneticinize başvurun.</p>';
            if ($cfg['app']['debug']) {
                echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            }
            exit;
        }
    }

    public static function get(): PDO
    {
        if (self::$pdo === null) {
            throw new \RuntimeException('Database not initialized.');
        }
        return self::$pdo;
    }
}
