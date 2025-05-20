<?php
/**
 * collector.php
 *
 * Receives traffic stats from MikroTik via HTTP GET and stores delta values in the SQLite database.
 */

require_once __DIR__ . '/vendor/autoload.php';

use DB\Database;
use Models\Device;
use Services\TrafficService;

$config = require __DIR__ . '/config.php';
$pdo = Database::getConnection($config['db_path']);

if (!isset($_GET['sn'], $_GET['tx'], $_GET['rx']) || !is_numeric($_GET['tx']) || !is_numeric($_GET['rx'])) {
    http_response_code(400);
    echo 'Invalid request';
    exit;
}

$sn = substr($_GET['sn'], 0, 12);
$tx = (int)$_GET['tx'];
$rx = (int)$_GET['rx'];

$deviceRepo = new Device($pdo);
$traffic = new TrafficService($pdo);

$device = $deviceRepo->findOrCreate($sn, $rx, $tx);

$rxDelta = $rx < $device['last_rx'] ? $rx : $rx - $device['last_rx'];
$txDelta = $tx < $device['last_tx'] ? $tx : $tx - $device['last_tx'];

$deviceRepo->update($device['id'], $rx, $tx);
$traffic->record($device['id'], $rxDelta, $txDelta);

echo 'OK';
