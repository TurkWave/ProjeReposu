<?php
// Model: Issue (sorun bildirme)
declare(strict_types=1);
namespace App\Models;
use App\Model;

class Issue extends Model
{
    protected string $table = 'issues';

    const CATEGORIES = [
        'other_duty' => 'Görev dışı iş',
        'safety'     => 'İş güvenliği',
        'behavior'   => 'Kötü davranış',
        'other'      => 'Diğer',
    ];

    /** Öğretmenin gördüğü tüm sorunlar (anonim ise öğrenci kimliği maske) */
    public function forTeacher(int $teacherId, string $status = 'all'): array
    {
        $where = 'i.teacher_id = ?';
        $params = [$teacherId];
        if ($status !== 'all') {
            $where .= ' AND iss.status = ?';
            $params[] = $status;
        }
        $sql = 'SELECT iss.*, s.name AS student_name, s.email AS student_email, i.id AS internship_id
                FROM issues iss
                JOIN internships i ON i.id = iss.internship_id
                JOIN users s       ON s.id = iss.reported_by
                WHERE ' . $where . '
                ORDER BY iss.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function forInternship(int $internshipId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM issues WHERE internship_id = ? ORDER BY created_at DESC');
        $stmt->execute([$internshipId]);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $sql = 'UPDATE issues SET status = ?';
        $args = [$status];
        if ($status === 'resolved') {
            $sql .= ', resolved_at = NOW()';
        }
        $sql .= ' WHERE id = ?';
        $args[] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($args);
    }
}
