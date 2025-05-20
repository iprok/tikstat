<?php

namespace Models;

use PDO;

class Device
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findOrCreate(string $sn, int $rx, int $tx): array
    {
        $stmt = $this->db->prepare("SELECT * FROM devices WHERE sn = :sn");
        $stmt->execute(['sn' => $sn]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($device) {
            return $device;
        }

        $stmt = $this->db->prepare("INSERT INTO devices (sn, last_check, last_rx, last_tx) VALUES (:sn, :check, :rx, :tx)");
        $stmt->execute([
            'sn' => $sn,
            'check' => time(),
            'rx' => $rx,
            'tx' => $tx
        ]);

        return [
            'id' => $this->db->lastInsertId(),
            'last_rx' => $rx,
            'last_tx' => $tx
        ];
    }

    public function update(int $id, int $rx, int $tx): void
    {
        $stmt = $this->db->prepare("UPDATE devices SET last_check = :check, last_rx = :rx, last_tx = :tx WHERE id = :id");
        $stmt->execute([
            'check' => time(),
            'rx' => $rx,
            'tx' => $tx,
            'id' => $id
        ]);
    }
}
