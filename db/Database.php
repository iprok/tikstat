<?php

namespace DB;

use PDO;
use PDOException;
use Services\MigrationService;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(string $path): PDO
    {
        if (self::$pdo === null) {
            try {
                $dbExists = file_exists($path);
                self::$pdo = new PDO("sqlite:$path");
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                if (!$dbExists || !self::isInitialized()) {
                    $migrator = new MigrationService(self::$pdo);
                    $migrator->migrate();
                }

            } catch (PDOException $e) {
                die("DB Connection failed: " . $e->getMessage());
            }
        }

        return self::$pdo;
    }

    private static function isInitialized(): bool
    {
        $result = self::$pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='devices'");
        return $result && $result->fetch() !== false;
    }
}
