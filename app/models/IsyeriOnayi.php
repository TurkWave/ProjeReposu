<?php
// Model: CompanyConfirmation (süreli onay linki + değerlendirme)
declare(strict_types=1);
namespace App\Models;
use App\Model;

class CompanyConfirmation extends Model
{
    protected string $table = 'company_confirmations';

    public function findByToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM company_confirmations WHERE token = ? LIMIT 1');
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public function forInternship(int $internshipId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM company_confirmations WHERE internship_id = ? LIMIT 1');
        $stmt->execute([$internshipId]);
        return $stmt->fetch() ?: null;
    }

    public function confirm(int $id, ?string $evaluation, ?string $feedback): bool
    {
        $stmt = $this->db->prepare('UPDATE company_confirmations SET confirmed = 1, confirmed_at = NOW(), evaluation = ?, feedback = ? WHERE id = ?');
        return $stmt->execute([$evaluation, $feedback, $id]);
    }
}
