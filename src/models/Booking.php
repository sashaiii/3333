<?php
declare(strict_types=1);
require_once __DIR__ . '/../db.php';

final class Booking
{
    public static function create(int $roomId, array $d): int
    {
        $stmt = db()->prepare(
            'INSERT INTO bookings
             (room_id, first_name, last_name, phone, email, check_in, check_out, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, "new")'
        );
        $stmt->execute([
            $roomId, $d['first_name'], $d['last_name'],
            $d['phone'], $d['email'], $d['check_in'], $d['check_out'],
        ]);
        return (int)db()->lastInsertId();
    }

    public static function all(): array
    {
        return db()->query(
            'SELECT b.*, r.room_number, rt.title AS room_title
             FROM bookings b
             JOIN rooms r ON r.id = b.room_id
             JOIN room_types rt ON rt.id = r.type_id
             ORDER BY b.created_at DESC'
        )->fetchAll();
    }

    public static function setStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['approved', 'rejected'], true)) return false;
        $stmt = db()->prepare(
            'UPDATE bookings SET status = ? WHERE id = ? AND status = "new"'
        );
        $stmt->execute([$status, $id]);
        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id): bool
    {
        $stmt = db()->prepare('DELETE FROM bookings WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}

public static function hasOverlap(int $roomId, string $checkIn, string $checkOut): bool
{
    // Ищем пересечение: новая бронь пересекается, если
    // (новый заезд < существующего выезда) И (новый выезд > существующего заезда)
    $sql = "SELECT COUNT(*) FROM bookings 
            WHERE room_id = ? 
              AND status = 'approved'
              AND check_in < ? 
              AND check_out > ?";
    
    $stmt = db()->prepare($sql);
    $stmt->execute([$roomId, $checkOut, $checkIn]);
    
    return (int)$stmt->fetchColumn() > 0;
}
