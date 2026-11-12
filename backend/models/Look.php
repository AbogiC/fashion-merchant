<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Curated outfits shown on the /mix-match page.
 */
class Look
{
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
}
