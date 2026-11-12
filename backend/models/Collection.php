<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Collection categories shown on the /collection page.
 */
class Collection
{
    public static function allActive(): array
    {
        $rows = Database::query(
            'SELECT id, name, season, pieces, monogram, image_url
               FROM collections
              WHERE is_active = 1
              ORDER BY sort_order ASC, id ASC',
        )->fetchAll();

        return array_map(static fn(array $row): array => [
            'id'        => (int) $row['id'],
            'name'      => $row['name'],
            'season'    => $row['season'],
            'pieces'    => (int) $row['pieces'],
            'monogram'  => $row['monogram'],
            'image_url' => $row['image_url'],
        ], $rows);
    }
}
