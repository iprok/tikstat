<?php

namespace Services;

use PDO;

class RawTrafficService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function log(int $deviceId, string $sn, string $iface, int $rx, int $tx): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO raw_traffic (device_id, sn, interface, rx, tx, ts)
            VALUES (:device_id, :sn, :interface, :rx, :tx, :ts)
        ");
        $stmt->execute([
            'device_id' => $deviceId,
            'sn' => $sn,
            'interface' => $iface,
            'rx' => $rx,
            'tx' => $tx,
            'ts' => time(),
        ]);
    }

}
