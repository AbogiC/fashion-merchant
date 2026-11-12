<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Style guidelines shown on the /guidelines page.
 */
class Guideline
{
    /** Public, active guidelines only (used by the marketing site). */
    public static function allActive(): array
    {
        $rows = Database::query(
            'SELECT id, number, title, description
               FROM guidelines
              WHERE is_active = 1
              ORDER BY sort_order ASC, id ASC',
        )->fetchAll();

        return array_map(static fn(array $row): array => [
            'id'          => (int) $row['id'],
            'number'      => $row['number'],
            'title'       => $row['title'],
            'description' => $row['description'],
        ], $rows);
    }

    /** All guidelines including inactive ones (admin dashboard). */
    public static function all(): array
    {
        $rows = Database::query(
            'SELECT * FROM guidelines ORDER BY sort_order ASC, id ASC',
        )->fetchAll();

        return array_map(static fn(array $row): array => self::cast($row), $rows);
    }

    public static function find(int $id): ?array
    {
        $row = Database::query(
            'SELECT * FROM guidelines WHERE id = ? LIMIT 1',
            [$id],
        )->fetch();

        return $row ? self::cast($row) : null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $pdo->prepare(
            'INSERT INTO guidelines (number, title, description, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?)',
        )->execute([
            $data['number'] ?? '',
            $data['title'],
            $data['description'] ?? '',
            (int) ($data['sort_order'] ?? 0),
            (int) ($data['is_active'] ?? 1),
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::query(
            'UPDATE guidelines
                SET number = ?, title = ?, description = ?, sort_order = ?, is_active = ?
              WHERE id = ?',
            [
                $data['number'] ?? '',
                $data['title'],
                $data['description'] ?? '',
                (int) ($data['sort_order'] ?? 0),
                (int) ($data['is_active'] ?? 1),
                $id,
            ],
        );
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM guidelines WHERE id = ?', [$id]);
    }

    private static function cast(array $row): array
    {
        return [
            'id'          => (int) $row['id'],
            'number'      => $row['number'],
            'title'       => $row['title'],
            'description' => $row['description'],
            'sort_order'  => (int) $row['sort_order'],
            'is_active'   => (bool) $row['is_active'],
            'created_at'  => $row['created_at'] ?? null,
            'updated_at'  => $row['updated_at'] ?? null,
        ];
    }
}
