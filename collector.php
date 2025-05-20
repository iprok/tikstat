<?php

require "init.php";

// Проверка входных параметров
if (isset($_GET['sn'])
    && isset($_GET['tx']) && is_numeric($_GET['tx'])
    && isset($_GET['rx']) && is_numeric($_GET['rx'])
) {
    $device_serial = substr($_GET['sn'], 0, 12);
    $rxNow = (int)$_GET['rx'];
    $txNow = (int)$_GET['tx'];
} else {
    echo 'fail';
    exit;
}

// Проверка устройства
$stmt = $pdo->prepare("SELECT id, last_tx, last_rx FROM devices WHERE sn = :sn");
$stmt->execute(['sn' => $device_serial]);
$device = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$device) {
    $stmt = $pdo->prepare("INSERT INTO devices (sn, last_check, last_tx, last_rx) VALUES (:sn, :check, :tx, :rx)");
    $stmt->execute(
        [
        'sn' => $device_serial,
        'check' => time(),
        'tx' => $txNow,
        'rx' => $rxNow
        ]
    );
    $device_id = $pdo->lastInsertId();
    $txBytes = $txNow;
    $rxBytes = $rxNow;
} else {
    $device_id = $device['id'];

    // Вычисление дельт
    $txBytes = ($txNow < $device['last_tx']) ? $txNow : $txNow - $device['last_tx'];
    $rxBytes = ($rxNow < $device['last_rx']) ? $rxNow : $rxNow - $device['last_rx'];

    // Обновление last_check/tx/rx
    $stmt = $pdo->prepare("UPDATE devices SET last_check = :check, last_tx = :tx, last_rx = :rx WHERE id = :id");
    $stmt->execute(
        [
        'check' => time(),
        'tx' => $txNow,
        'rx' => $rxNow,
        'id' => $device_id
        ]
    );
}

// Почасовая агрегация
$hour_ts = mktime(date('H'), 0, 0);
$stmt = $pdo->prepare("SELECT id, tx, rx FROM traffic WHERE device_id = :id AND datetime = :dt");
$stmt->execute(['id' => $device_id, 'dt' => $hour_ts]);
$entry = $stmt->fetch(PDO::FETCH_ASSOC);

if ($entry) {
    $stmt = $pdo->prepare("UPDATE traffic SET tx = tx + :tx, rx = rx + :rx WHERE id = :id");
    $stmt->execute(
        [
        'tx' => $txBytes,
        'rx' => $rxBytes,
        'id' => $entry['id']
        ]
    );
} else {
    $stmt = $pdo->prepare("INSERT INTO traffic (device_id, datetime, tx, rx) VALUES (:id, :dt, :tx, :rx)");
    $stmt->execute(
        [
        'id' => $device_id,
        'dt' => $hour_ts,
        'tx' => $txBytes,
        'rx' => $rxBytes
        ]
    );
}
