<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Journal entries shown on the /news page.
 */
class NewsArticle
{
    /** Public, active articles only (used by the marketing site). */
    public static function allActive(): array
    {
        $rows = Database::query(
            'SELECT id, slug, title, excerpt, image_url, published_at
               FROM news
              WHERE is_active = 1
              ORDER BY published_at IS NULL ASC, published_at DESC, sort_order ASC, id DESC',
        )->fetchAll();

        return array_map(static function (array $row): array {
            $published = $row['published_at'] ?? null;

            return [
                'id'        => (int) $row['id'],
                'slug'      => $row['slug'],
                'title'     => $row['title'],
                'excerpt'   => $row['excerpt'],
                'image_url' => $row['image_url'],
                'date'      => $published ? date('F Y', strtotime((string) $published)) : null,
            ];
        }, $rows);
    }

    /** All articles including inactive ones, with full body (admin dashboard). */
    public static function all(): array
    {
        $rows = Database::query(
            'SELECT * FROM news
              ORDER BY published_at IS NULL ASC, published_at DESC, sort_order ASC, id DESC',
        )->fetchAll();

        return array_map(static fn(array $row): array => self::cast($row), $rows);
    }

    public static function find(int $id): ?array
    {
        $row = Database::query(
            'SELECT * FROM news WHERE id = ? LIMIT 1',
            [$id],
        )->fetch();

        return $row ? self::cast($row) : null;
    }

    /** True when another article already uses this slug. */
    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId === null) {
            $count = Database::query(
                'SELECT COUNT(*) AS n FROM news WHERE slug = ?',
                [$slug],
            )->fetch();
        } else {
            $count = Database::query(
                'SELECT COUNT(*) AS n FROM news WHERE slug = ? AND id != ?',
                [$slug, $excludeId],
            )->fetch();
        }

        return (int) ($count['n'] ?? 0) > 0;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $pdo->prepare(
            'INSERT INTO news (slug, title, excerpt, body, image_url, published_at, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        )->execute([
            $data['slug'],
            $data['title'],
            $data['excerpt'] ?? '',
            self::nullableLong($data['body'] ?? null),
            self::nullable($data['image_url'] ?? null),
            self::nullableDate($data['published_at'] ?? null),
            (int) ($data['sort_order'] ?? 0),
            (int) ($data['is_active'] ?? 1),
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::query(
            'UPDATE news
                SET slug = ?, title = ?, excerpt = ?, body = ?, image_url = ?,
                    published_at = ?, sort_order = ?, is_active = ?
              WHERE id = ?',
            [
                $data['slug'],
                $data['title'],
                $data['excerpt'] ?? '',
                self::nullableLong($data['body'] ?? null),
                self::nullable($data['image_url'] ?? null),
                self::nullableDate($data['published_at'] ?? null),
                (int) ($data['sort_order'] ?? 0),
                (int) ($data['is_active'] ?? 1),
                $id,
            ],
        );
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM news WHERE id = ?', [$id]);
    }

    private static function nullable(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = (string) $value;
        return $value === '' ? null : $value;
    }

    /** The body column may legitimately store an empty string, so keep it. */
    private static function nullableLong(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        return (string) $value;
    }

    private static function nullableDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (string) $value;
    }

    private static function cast(array $row): array
    {
        return [
            'id'           => (int) $row['id'],
            'slug'          => $row['slug'],
            'title'         => $row['title'],
            'excerpt'       => $row['excerpt'],
            'body'          => $row['body'],
            'image_url'     => $row['image_url'],
            'published_at'  => $row['published_at'],
            'sort_order'    => (int) $row['sort_order'],
            'is_active'     => (bool) $row['is_active'],
            'created_at'    => $row['created_at'] ?? null,
            'updated_at'    => $row['updated_at'] ?? null,
        ];
    }
}
