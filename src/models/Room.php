<?php
declare(strict_types=1);
require_once __DIR__ . '/../db.php';

final class Room
{
    public static function findWithFilter(array $f, int $limit = 6): array
    {
        $sql = 'SELECT rt.*, r.id AS room_id, r.room_number
                FROM rooms r JOIN room_types rt ON rt.id = r.type_id
                WHERE 1=1';
        $p = [];

        if (!empty($f['category']) && in_array($f['category'], ['standard', 'studio', 'lux'], true)) {
            $sql .= ' AND rt.code = :cat';
            $p[':cat'] = $f['category'];
        }
        if (($f['price_min'] ?? '') !== '') {
            $sql .= ' AND rt.price_per_day >= :pmin';
            $p[':pmin'] = (float)$f['price_min'];
        }
        if (($f['price_max'] ?? '') !== '') {
            $sql .= ' AND rt.price_per_day <= :pmax';
            $p[':pmax'] = (float)$f['price_max'];
        }
        if (!empty($f['capacity'])) {
            $c = (int)$f['capacity'];
            if ($c >= 3) {
                $sql .= ' AND rt.capacity >= 3';
            } else {
                $sql .= ' AND rt.capacity >= :cap';
                $p[':cap'] = $c;
            }
        }

        $sql .= ' ORDER BY RAND() LIMIT ' . (int)$limit;

        $stmt = db()->prepare($sql);
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public static function findRoom(int $roomId): ?array
    {
        $stmt = db()->prepare(
            'SELECT r.id AS room_id, r.room_number, rt.*
             FROM rooms r JOIN room_types rt ON rt.id = r.type_id
             WHERE r.id = ? LIMIT 1'
        );
        $stmt->execute([$roomId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
