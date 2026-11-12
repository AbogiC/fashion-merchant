<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Journal entries shown on the /news page.
 */
class NewsArticle
{
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
}
