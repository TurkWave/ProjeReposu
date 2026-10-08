<?php
// Model: Journal (staj defteri)
declare(strict_types=1);
namespace App\Models;
use App\Model;

class Journal extends Model
{
    protected string $table = 'journal';

    public function findByInternshipAndDate(int $internshipId, string $date): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM journal WHERE internship_id = ? AND date = ?');
        $stmt->execute([$internshipId, $date]);
        return $stmt->fetch() ?: null;
    }

    public function forInternship(int $internshipId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM journal WHERE internship_id = ? ORDER BY date DESC');
        $stmt->execute([$internshipId]);
        return $stmt->fetchAll();
    }
}
