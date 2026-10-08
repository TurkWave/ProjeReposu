<?php
// Model: FaqItem (SSS)
declare(strict_types=1);
namespace App\Models;
use App\Model;

class FaqItem extends Model
{
    protected string $table = 'faq_items';

    public function active(): array
    {
        $stmt = $this->db->query("SELECT * FROM faq_items WHERE active = 1 ORDER BY sort_order ASC, id ASC");
        return $stmt->fetchAll();
    }
}
