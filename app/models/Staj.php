<?php
// ============================================================
// Model: Internship
// (Öğrenci <-> işletme <-> öğretmen)
// RLS: teacher_id / student_id filtreleri burada uygulanır.
// ============================================================
declare(strict_types=1);

namespace App\Models;

use App\Model;

class Internship extends Model
{
    protected string $table = 'internships';

    /** Bir öğretmene ait tüm stajları (öğrenci + işletme ile). */
    public function forTeacher(int $teacherId): array
    {
        $sql = 'SELECT i.*, u.name AS student_name, c.name AS company_name, cc.token AS confirm_token,
                       cc.confirmed AS company_confirmed_confirm, cc.expires_at
                FROM internships i
                JOIN users u     ON u.id = i.student_id
                JOIN companies c ON c.id = i.company_id
                LEFT JOIN company_confirmations cc ON cc.internship_id = i.id
                WHERE i.teacher_id = ?
                ORDER BY i.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }

    /** Öğrencinin kendi stajı. */
    public function forStudent(int $studentId): ?array
    {
        $sql = 'SELECT i.*, c.name AS company_name, cc.confirmed AS company_confirmed_confirm, cc.token AS confirm_token
                FROM internships i
                JOIN companies c ON c.id = i.company_id
                LEFT JOIN company_confirmations cc ON cc.internship_id = i.id
                WHERE i.student_id = ? ORDER BY i.created_at DESC LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetch() ?: null;
    }

    public function get(int $id): ?array
    {
        $sql = 'SELECT i.*, s.name AS student_name, s.email AS student_email,
                       c.name AS company_name, t.name AS teacher_name
                FROM internships i
                JOIN users s ON s.id = i.student_id
                JOIN companies c ON c.id = i.company_id
                JOIN users t ON t.id = i.teacher_id
                WHERE i.id = ? LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function confirmCompany(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE internships SET company_confirmed = 1, status = ? WHERE id = ?');
        return $stmt->execute(['active', $id]);
    }

    public function addComment(int $id, ?string $comment): bool
    {
        $stmt = $this->db->prepare('UPDATE internships SET teacher_comment = ? WHERE id = ?');
        return $stmt->execute([$comment, $id]);
    }
}
