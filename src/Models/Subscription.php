<?php

namespace App\Models;

use PDO;

class Subscription
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(string $email, string $city, string $frequency, string $token): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (email, city, frequency, token, confirmed) VALUES (?, ?, ?, ?, false)'
        );

        return $stmt->execute([$email, $city, $frequency, $token]);
    }

    public function findByToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE token = ?');
        $stmt->execute([$token]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function confirmByToken(string $token): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET confirmed = true WHERE token = ?');
        return $stmt->execute([$token]);
    }

    public function deleteByToken(string $token): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE token = ?');
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }
}

