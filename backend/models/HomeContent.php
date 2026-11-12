<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Aggregates the editable content for the home page.
 */
class HomeContent
{
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

    private static function settings(): array
    {
        $row = Database::query('SELECT * FROM home_settings WHERE id = 1 LIMIT 1')->fetch();

        if (!$row) {
            return [
                'hero_eyebrow'             => '',
                'hero_title_primary'       => '',
                'hero_title_secondary'     => '',
                'hero_description'         => '',
                'hero_cta_label'           => '',
                'hero_cta_url'             => '/collection',
                'philosophy_subtitle'      => '',
                'philosophy_title_primary' => '',
                'philosophy_title_secondary' => '',
                'philosophy_body'          => '',
                'philosophy_cta_label'     => '',
                'philosophy_cta_url'       => '/about',
                'philosophy_monogram'      => 'M',
            ];
        }

        return $row;
    }

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
