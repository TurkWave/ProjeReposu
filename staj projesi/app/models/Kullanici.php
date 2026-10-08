<?php
// ============================================================
// Model: User
// ============================================================
declare(strict_types=1);

namespace App\Models;

use App\Model;

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function allStudents(): array
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE role='student' ORDER BY name");
        return $stmt->fetchAll();
    }

    public function teachers(): array
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE role='teacher' ORDER BY name");
        return $stmt->fetchAll();
    }
}
