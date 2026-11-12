<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Aggregates the editable content for the home page.
 *
 * The home page is composed of a single settings row plus three list tables
 * (featured cards, stats and marquee words). The public payload() method
 * returns only active items; the admin methods return everything so the
 * dashboard can show, reorder and toggle each piece.
 */
class HomeContent
{
    // ------------------------------------------------------------------
    // Public payload (read by the marketing site)
    // ------------------------------------------------------------------

    public static function payload(): array
    {
        $settings = self::settings();

        return [
            'hero' => [
                'eyebrow'         => $settings['hero_eyebrow'],
                'title_primary'   => $settings['hero_title_primary'],
                'title_secondary' => $settings['hero_title_secondary'],
                'description'     => $settings['hero_description'],
                'cta_label'       => $settings['hero_cta_label'],
                'cta_url'         => $settings['hero_cta_url'],
            ],
            'featured' => self::featured(),
            'stats'    => self::stats(),
            'marquee'  => self::marquee(),
            'philosophy' => [
                'subtitle'        => $settings['philosophy_subtitle'],
                'title_primary'   => $settings['philosophy_title_primary'],
                'title_secondary' => $settings['philosophy_title_secondary'],
                'body'            => self::paragraphs($settings['philosophy_body']),
                'cta_label'       => $settings['philosophy_cta_label'],
                'cta_url'         => $settings['philosophy_cta_url'],
                'monogram'        => $settings['philosophy_monogram'],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Settings (single row, id = 1)
    // ------------------------------------------------------------------

    private static function settings(): array
    {
        $row = Database::query('SELECT * FROM home_settings WHERE id = 1 LIMIT 1')->fetch();

        if (!$row) {
            return [
                'hero_eyebrow'               => '',
                'hero_title_primary'         => '',
                'hero_title_secondary'       => '',
                'hero_description'           => '',
                'hero_cta_label'             => '',
                'hero_cta_url'               => '/collection',
                'philosophy_subtitle'        => '',
                'philosophy_title_primary'   => '',
                'philosophy_title_secondary' => '',
                'philosophy_body'            => '',
                'philosophy_cta_label'       => '',
                'philosophy_cta_url'         => '/about',
                'philosophy_monogram'        => 'M',
            ];
        }

        return $row;
    }

    /** Full settings row for the admin editor (includes id). */
    public static function settingsRow(): array
    {
        return self::settings();
    }

    /** Upsert the single settings row (id = 1). */
    public static function updateSettings(array $d): void
    {
        // Ensure the row exists before updating it.
        Database::query('INSERT IGNORE INTO home_settings (id) VALUES (1)');

        Database::query(
            'UPDATE home_settings SET
                hero_eyebrow               = ?,
                hero_title_primary         = ?,
                hero_title_secondary       = ?,
                hero_description           = ?,
                hero_cta_label             = ?,
                hero_cta_url               = ?,
                philosophy_subtitle        = ?,
                philosophy_title_primary   = ?,
                philosophy_title_secondary = ?,
                philosophy_body            = ?,
                philosophy_cta_label       = ?,
                philosophy_cta_url         = ?,
                philosophy_monogram        = ?
              WHERE id = 1',
            [
                $d['hero_eyebrow'] ?? '',
                $d['hero_title_primary'] ?? '',
                $d['hero_title_secondary'] ?? '',
                $d['hero_description'] ?? '',
                $d['hero_cta_label'] ?? '',
                $d['hero_cta_url'] ?? '/collection',
                $d['philosophy_subtitle'] ?? '',
                $d['philosophy_title_primary'] ?? '',
                $d['philosophy_title_secondary'] ?? '',
                $d['philosophy_body'] ?? '',
                $d['philosophy_cta_label'] ?? '',
                $d['philosophy_cta_url'] ?? '/about',
                $d['philosophy_monogram'] ?? 'M',
            ],
        );
    }

    // ------------------------------------------------------------------
    // Featured cards
    // ------------------------------------------------------------------

    private static function featured(): array
    {
        $rows = Database::query(
            'SELECT id, tag, title, copy, monogram, image_url, link_url
               FROM home_featured
              WHERE is_active = 1
              ORDER BY sort_order ASC, id ASC',
        )->fetchAll();

        return array_map(static fn(array $row): array => [
            'id'        => (int) $row['id'],
            'tag'       => $row['tag'],
            'title'     => $row['title'],
            'copy'      => $row['copy'],
            'monogram'  => $row['monogram'],
            'image_url' => $row['image_url'],
            'link_url'  => $row['link_url'],
        ], $rows);
    }

    public static function featuredAll(): array
    {
        $rows = Database::query(
            'SELECT * FROM home_featured ORDER BY sort_order ASC, id ASC',
        )->fetchAll();

        return array_map(static fn(array $row): array => self::castFeatured($row), $rows);
    }

    public static function featuredFind(int $id): ?array
    {
        $row = Database::query(
            'SELECT * FROM home_featured WHERE id = ? LIMIT 1',
            [$id],
        )->fetch();

        return $row ? self::castFeatured($row) : null;
    }

    public static function featuredCreate(array $data): int
    {
        $pdo = Database::connection();
        $pdo->prepare(
            'INSERT INTO home_featured (tag, title, copy, monogram, image_url, link_url, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        )->execute([
            $data['tag'] ?? '',
            $data['title'] ?? '',
            $data['copy'] ?? '',
            self::nullable($data['monogram'] ?? null),
            self::nullable($data['image_url'] ?? null),
            $data['link_url'] ?? '/collection',
            (int) ($data['sort_order'] ?? 0),
            (int) ($data['is_active'] ?? 1),
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function featuredUpdate(int $id, array $data): void
    {
        Database::query(
            'UPDATE home_featured
                SET tag = ?, title = ?, copy = ?, monogram = ?, image_url = ?,
                    link_url = ?, sort_order = ?, is_active = ?
              WHERE id = ?',
            [
                $data['tag'] ?? '',
                $data['title'] ?? '',
                $data['copy'] ?? '',
                self::nullable($data['monogram'] ?? null),
                self::nullable($data['image_url'] ?? null),
                $data['link_url'] ?? '/collection',
                (int) ($data['sort_order'] ?? 0),
                (int) ($data['is_active'] ?? 1),
                $id,
            ],
        );
    }

    public static function featuredDelete(int $id): void
    {
        Database::query('DELETE FROM home_featured WHERE id = ?', [$id]);
    }

    private static function castFeatured(array $row): array
    {
        return [
            'id'         => (int) $row['id'],
            'tag'        => $row['tag'],
            'title'      => $row['title'],
            'copy'       => $row['copy'],
            'monogram'   => $row['monogram'],
            'image_url'  => $row['image_url'],
            'link_url'   => $row['link_url'],
            'sort_order' => (int) $row['sort_order'],
            'is_active'  => (bool) $row['is_active'],
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }

    // ------------------------------------------------------------------
    // Stats
    // ------------------------------------------------------------------

    private static function stats(): array
    {
        $rows = Database::query(
            'SELECT id, value, suffix, label
               FROM home_stats
              WHERE is_active = 1
              ORDER BY sort_order ASC, id ASC',
        )->fetchAll();

        return array_map(static fn(array $row): array => [
            'id'     => (int) $row['id'],
            'value'  => $row['value'],
            'suffix' => $row['suffix'],
            'label'  => $row['label'],
        ], $rows);
    }

    public static function statsAll(): array
    {
        $rows = Database::query(
            'SELECT * FROM home_stats ORDER BY sort_order ASC, id ASC',
        )->fetchAll();

        return array_map(static fn(array $row): array => self::castStats($row), $rows);
    }

    public static function statsFind(int $id): ?array
    {
        $row = Database::query(
            'SELECT * FROM home_stats WHERE id = ? LIMIT 1',
            [$id],
        )->fetch();

        return $row ? self::castStats($row) : null;
    }

    public static function statsCreate(array $data): int
    {
        $pdo = Database::connection();
        $pdo->prepare(
            'INSERT INTO home_stats (value, suffix, label, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?)',
        )->execute([
            $data['value'] ?? '',
            $data['suffix'] ?? '',
            $data['label'] ?? '',
            (int) ($data['sort_order'] ?? 0),
            (int) ($data['is_active'] ?? 1),
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function statsUpdate(int $id, array $data): void
    {
        Database::query(
            'UPDATE home_stats
                SET value = ?, suffix = ?, label = ?, sort_order = ?, is_active = ?
              WHERE id = ?',
            [
                $data['value'] ?? '',
                $data['suffix'] ?? '',
                $data['label'] ?? '',
                (int) ($data['sort_order'] ?? 0),
                (int) ($data['is_active'] ?? 1),
                $id,
            ],
        );
    }

    public static function statsDelete(int $id): void
    {
        Database::query('DELETE FROM home_stats WHERE id = ?', [$id]);
    }

    private static function castStats(array $row): array
    {
        return [
            'id'         => (int) $row['id'],
            'value'      => $row['value'],
            'suffix'     => $row['suffix'],
            'label'      => $row['label'],
            'sort_order' => (int) $row['sort_order'],
            'is_active'  => (bool) $row['is_active'],
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }

    // ------------------------------------------------------------------
    // Marquee
    // ------------------------------------------------------------------

    private static function marquee(): array
    {
        $rows = Database::query(
            'SELECT word
               FROM home_marquee
              WHERE is_active = 1
              ORDER BY sort_order ASC, id ASC',
        )->fetchAll();

        return array_map(static fn(array $row): string => $row['word'], $rows);
    }

    public static function marqueeAll(): array
    {
        $rows = Database::query(
            'SELECT * FROM home_marquee ORDER BY sort_order ASC, id ASC',
        )->fetchAll();

        return array_map(static fn(array $row): array => self::castMarquee($row), $rows);
    }

    public static function marqueeFind(int $id): ?array
    {
        $row = Database::query(
            'SELECT * FROM home_marquee WHERE id = ? LIMIT 1',
            [$id],
        )->fetch();

        return $row ? self::castMarquee($row) : null;
    }

    public static function marqueeCreate(array $data): int
    {
        $pdo = Database::connection();
        $pdo->prepare(
            'INSERT INTO home_marquee (word, sort_order, is_active)
             VALUES (?, ?, ?)',
        )->execute([
            $data['word'] ?? '',
            (int) ($data['sort_order'] ?? 0),
            (int) ($data['is_active'] ?? 1),
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function marqueeUpdate(int $id, array $data): void
    {
        Database::query(
            'UPDATE home_marquee SET word = ?, sort_order = ?, is_active = ? WHERE id = ?',
            [
                $data['word'] ?? '',
                (int) ($data['sort_order'] ?? 0),
                (int) ($data['is_active'] ?? 1),
                $id,
            ],
        );
    }

    public static function marqueeDelete(int $id): void
    {
        Database::query('DELETE FROM home_marquee WHERE id = ?', [$id]);
    }

    private static function castMarquee(array $row): array
    {
        return [
            'id'         => (int) $row['id'],
            'word'       => $row['word'],
            'sort_order' => (int) $row['sort_order'],
            'is_active'  => (bool) $row['is_active'],
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /** Normalise a value into NULL when it is an empty string. */
    private static function nullable(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = (string) $value;
        return $value === '' ? null : $value;
    }

    /** Split the stored body into paragraphs separated by blank lines. */
    private static function paragraphs(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        $parts = preg_split('/\r?\n\s*\r?\n/', $text) ?: [];

        return array_values(array_filter(
            array_map('trim', $parts),
            static fn(string $part): bool => $part !== '',
        ));
    }
}
