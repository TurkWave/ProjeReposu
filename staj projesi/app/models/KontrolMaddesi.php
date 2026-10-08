<?php
// Model: ChecklistItem (staj kontrol listesi)
declare(strict_types=1);
namespace App\Models;
use App\Model;

class ChecklistItem extends Model
{
    protected string $table = 'checklist_items';

    public function active(): array
    {
        $stmt = $this->db->query("SELECT * FROM checklist_items WHERE active = 1 ORDER BY sort_order ASC, id ASC");
        return $stmt->fetchAll();
    }
}
