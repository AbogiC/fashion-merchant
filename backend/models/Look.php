<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Curated outfits shown on the /mix-match page.
 */
class Look
{
    /** Public, active looks only (used by the marketing site). */
    public static function allActive(): array
    {
        $rows = Database::query(
            'SELECT id, label, title, description, pieces, monogram, image_url
               FROM looks
              WHERE is_active = 1
              ORDER BY sort_order ASC, id ASC',
        )->fetchAll();

        return array_map(static fn(array $row): array => [
            'id'          => (int) $row['id'],
            'label'       => $row['label'],
            'title'       => $row['title'],
            'description' => $row['description'],
            'pieces'      => $row['pieces'],
            'monogram'    => $row['monogram'],
            'image_url'   => $row['image_url'],
        ], $rows);
    }

    /** All looks including inactive ones (admin dashboard). */
    public static function all(): array
    {
        $rows = Database::query(
            'SELECT * FROM looks ORDER BY sort_order ASC, id ASC',
        )->fetchAll();

        return array_map(static fn(array $row): array => self::cast($row), $rows);
    }

    public static function find(int $id): ?array
    {
        $row = Database::query(
            'SELECT * FROM looks WHERE id = ? LIMIT 1',
            [$id],
        )->fetch();

        return $row ? self::cast($row) : null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $pdo->prepare(
            'INSERT INTO looks (label, title, description, pieces, monogram, image_url, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        )->execute([
            $data['label'] ?? '',
            $data['title'],
            $data['description'] ?? '',
            $data['pieces'] ?? '',
            self::nullable($data['monogram'] ?? null),
            self::nullable($data['image_url'] ?? null),
            (int) ($data['sort_order'] ?? 0),
            (int) ($data['is_active'] ?? 1),
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::query(
            'UPDATE looks
                SET label = ?, title = ?, description = ?, pieces = ?, monogram = ?,
                    image_url = ?, sort_order = ?, is_active = ?
              WHERE id = ?',
            [
                $data['label'] ?? '',
                $data['title'],
                $data['description'] ?? '',
                $data['pieces'] ?? '',
                self::nullable($data['monogram'] ?? null),
                self::nullable($data['image_url'] ?? null),
                (int) ($data['sort_order'] ?? 0),
                (int) ($data['is_active'] ?? 1),
                $id,
            ],
        );
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM looks WHERE id = ?', [$id]);
    }

    private static function nullable(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = (string) $value;
        return $value === '' ? null : $value;
    }

    private static function cast(array $row): array
    {
        return [
            'id'          => (int) $row['id'],
            'label'       => $row['label'],
            'title'       => $row['title'],
            'description' => $row['description'],
            'pieces'      => $row['pieces'],
            'monogram'    => $row['monogram'],
            'image_url'   => $row['image_url'],
            'sort_order'  => (int) $row['sort_order'],
            'is_active'   => (bool) $row['is_active'],
            'created_at'  => $row['created_at'] ?? null,
            'updated_at'  => $row['updated_at'] ?? null,
        ];
    }
}
