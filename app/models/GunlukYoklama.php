<?php
// Model: DailyRecord (günlük yoklama)
declare(strict_types=1);
namespace App\Models;
use App\Model;

class DailyRecord extends Model
{
    protected string $table = 'daily_records';

    /** Öğrencinin bu tarih için girişi (RLS: öğrenci kendi verisi). */
    public function findByInternshipAndDate(int $internshipId, string $date): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM daily_records WHERE internship_id = ? AND date = ?');
        $stmt->execute([$internshipId, $date]);
        return $stmt->fetch() ?: null;
    }

    public function forInternship(int $internshipId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM daily_records WHERE internship_id = ? ORDER BY date DESC');
        $stmt->execute([$internshipId]);
        return $stmt->fetchAll();
    }
}
