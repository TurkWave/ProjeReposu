<?php
// Model: Company (işletme)
declare(strict_types=1);
namespace App\Models;
use App\Model;

class Company extends Model
{
    protected string $table = 'companies';

    public function findByName(string $name): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM companies WHERE name = ? LIMIT 1');
        $stmt->execute([$name]);
        return $stmt->fetch() ?: null;
    }
}
