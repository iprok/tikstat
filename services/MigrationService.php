<?php

namespace Services;

use PDO;

class MigrationService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function migrate(): void
    {
        $this->createDevicesTable();
        $this->createTrafficTable();
        $this->createTrafficSummaryTable();
        $this->createRawTrafficTable();
    }

    private function createDevicesTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS devices (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sn TEXT UNIQUE,
                comment TEXT,
                last_check INTEGER,
                last_tx INTEGER,
                last_rx INTEGER
            )
        ");
    }

    private function createTrafficTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS traffic (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                device_id INTEGER,
                interface TEXT,
                datetime INTEGER,
                tx INTEGER,
                rx INTEGER,
                UNIQUE(device_id, interface, datetime)
            )
        ");
    }

    private function createTrafficSummaryTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS traffic_summary (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                device_id INTEGER,
                interface TEXT,
                day INTEGER,
                month INTEGER,
                year INTEGER,
                tx INTEGER,
                rx INTEGER,
                UNIQUE(device_id, interface, day, month, year)
            )
        ");
    }


    private function createRawTrafficTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS raw_traffic (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                device_id INTEGER NOT NULL,
                sn TEXT NOT NULL,
                interface TEXT NOT NULL,
                rx INTEGER NOT NULL,
                tx INTEGER NOT NULL,
                ts INTEGER NOT NULL DEFAULT (strftime('%s', 'now'))
            )
        ");
    }

}
