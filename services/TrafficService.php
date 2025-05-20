<?php

namespace Services;

use PDO;

class TrafficService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function record(int $deviceId, int $rxDelta, int $txDelta): void
    {
        $hour = mktime(date('H'), 0, 0);
        $stmt = $this->db->prepare("SELECT id FROM traffic WHERE device_id = :id AND datetime = :dt");
        $stmt->execute(['id' => $deviceId, 'dt' => $hour]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($entry) {
            $stmt = $this->db->prepare("UPDATE traffic SET tx = tx + :tx, rx = rx + :rx WHERE id = :id");
            $stmt->execute([
                'tx' => $txDelta,
                'rx' => $rxDelta,
                'id' => $entry['id']
            ]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO traffic (device_id, datetime, tx, rx) VALUES (:id, :dt, :tx, :rx)");
            $stmt->execute([
                'id' => $deviceId,
                'dt' => $hour,
                'tx' => $txDelta,
                'rx' => $rxDelta
            ]);
        }
    }
}
