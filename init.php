<?php

/*
    Инициализируем соединение с базой, создаём таблицы если их нет
*/

try {
    $pdo = new PDO('sqlite:data/tikstat.sqlite.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Создание таблицы устройств
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS devices (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sn TEXT UNIQUE,
            comment TEXT,
            last_check INTEGER,
            last_tx INTEGER,
            last_rx INTEGER
        )"
    );

    // Создание таблицы почасового трафика
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS traffic (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          device_id INTEGER,
          datetime INTEGER,
          tx INTEGER,
          rx INTEGER,
          UNIQUE(device_id, datetime)
        )"
    );

    // Создание таблицы помесячного суммарного трафика (по желанию)
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS traffic_summary (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          device_id INTEGER,
          day INTEGER,
          month INTEGER,
          year INTEGER,
          tx INTEGER,
          rx INTEGER,
          UNIQUE(device_id, day, month, year)
        )"
    );

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}
