<?php
// ============================================================
// Staj Takip - Temel Model (PDO tabanlı, güvenli)
// ============================================================
declare(strict_types=1);

namespace App;

use PDO;

abstract class Model
{
    protected PDO $db;
    protected string $table;

    public function __construct()
    {
        $this->db = Database::get();
        // $table açıkça atanmamışsa (örn. User -> users) sınıf adından türet.
        // Açıkça verilmişse ONA GÖRE BIRAK (çoplacalık 's' ekleme YOK).
        if (!isset($this->table)) {
            $name = (new \ReflectionClass($this))->getShortName();
            $this->table = strtolower(preg_replace('/(.+)\B([A-Z])/', '$1_$2', $name)) . 's';
        }
    }

    protected function table(): string
    {
        return $this->table;
    }

    /** Tek satır getir. */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM ' . $this->table() . ' WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Tüm satırlar. */
    public function all(string $order = 'id ASC'): array
    {
        $stmt = $this->db->query('SELECT * FROM ' . $this->table() . ' ORDER BY ' . $order);
        return $stmt->fetchAll();
    }

    /** Ekle. @return yeni satır id (int) */
    public function insert(array $data): int
    {
        $cols = array_keys($data);
        $place = array_fill(0, count($cols), '?');
        $sql = 'INSERT INTO ' . $this->table() . ' (' . implode(',', $cols) . ') VALUES (' . implode(',', $place) . ')';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        return (int)$this->db->lastInsertId();
    }

    /** Güncelle. */
    public function update(int $id, array $data): bool
    {
        if (!$data) return true;
        $cols = array_keys($data);
        $set = [];
        foreach ($cols as $c) $set[] = $c . ' = ?';
        $sql = 'UPDATE ' . $this->table() . ' SET ' . implode(',', $set) . ' WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([...array_values($data), $id]);
    }

    /** Sil. */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM ' . $this->table() . ' WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
