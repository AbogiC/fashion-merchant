<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Style guidelines shown on the /guidelines page.
 */
class Guideline
{
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
}
