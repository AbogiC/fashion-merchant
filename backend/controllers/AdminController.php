<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Collection;
use App\Models\Guideline;
use App\Models\HomeContent;
use App\Models\Look;
use App\Models\NewsArticle;
use App\Utils\Validator;

/**
 * Admin content management endpoints.
 *
 * Every route here is protected by AuthMiddleware + AdminMiddleware, so by
 * the time a handler runs the request is both authenticated and an admin.
 *
 * Validation uses the fluent Validator for required/string fields and a few
 * inline checks for types. Models centralise all SQL (parameterised) and the
 * coercion of booleans/integers, so controllers stay focused on request flow.
 */
class AdminController
{
    // ==================================================================
    // Collections
    // ==================================================================

    public function collectionsIndex(Request $request): void
    {
        Response::json(['collections' => Collection::all()]);
    }

    public function collectionShow(Request $request): void
    {
        $item = Collection::find($this->idParam($request));
        $this->requireFound($item);
        Response::json($item);
    }

    public function collectionStore(Request $request): void
    {
        $data = $this->validateCollection($request->body());
        $id = Collection::create($data);
        Response::json(Collection::find($id), 201);
    }

    public function collectionUpdate(Request $request): void
    {
        $id = $this->idParam($request);
        $this->requireFound(Collection::find($id));
        $data = $this->validateCollection($request->body());
        Collection::update($id, $data);
        Response::json(Collection::find($id));
    }

    public function collectionDestroy(Request $request): void
    {
        $id = $this->idParam($request);
        $this->requireFound(Collection::find($id));
        Collection::delete($id);
        Response::json(['message' => 'Collection deleted.']);
    }

    private function validateCollection(array $d): array
    {
        $v = Validator::make($d)
            ->required('name')->max('name', 120)
            ->max('season', 60)
            ->max('monogram', 8)
            ->max('image_url', 500)
            ->integer('pieces')
            ->integer('sort_order');

        if ($v->fails()) {
            Response::error('The given data was invalid.', 422, $v->errors());
        }

        return [
            'name'       => trim((string) $d['name']),
            'season'     => (string) ($d['season'] ?? ''),
            'pieces'     => (int) ($d['pieces'] ?? 0),
            'monogram'   => $d['monogram'] ?? null,
            'image_url'  => $d['image_url'] ?? null,
            'sort_order' => (int) ($d['sort_order'] ?? 0),
            'is_active'  => !empty($d['is_active']),
        ];
    }

    // ==================================================================
    // Guidelines
    // ==================================================================

    public function guidelinesIndex(Request $request): void
    {
        Response::json(['guidelines' => Guideline::all()]);
    }

    public function guidelineShow(Request $request): void
    {
        $item = Guideline::find($this->idParam($request));
        $this->requireFound($item);
        Response::json($item);
    }

    public function guidelineStore(Request $request): void
    {
        $data = $this->validateGuideline($request->body());
        $id = Guideline::create($data);
        Response::json(Guideline::find($id), 201);
    }

    public function guidelineUpdate(Request $request): void
    {
        $id = $this->idParam($request);
        $this->requireFound(Guideline::find($id));
        $data = $this->validateGuideline($request->body());
        Guideline::update($id, $data);
        Response::json(Guideline::find($id));
    }

    public function guidelineDestroy(Request $request): void
    {
        $id = $this->idParam($request);
        $this->requireFound(Guideline::find($id));
        Guideline::delete($id);
        Response::json(['message' => 'Guideline deleted.']);
    }

    private function validateGuideline(array $d): array
    {
        $v = Validator::make($d)
            ->required('title')->max('title', 150)
            ->max('number', 10)
            ->integer('sort_order');

        if ($v->fails()) {
            Response::error('The given data was invalid.', 422, $v->errors());
        }

        return [
            'number'      => (string) ($d['number'] ?? ''),
            'title'       => trim((string) $d['title']),
            'description' => (string) ($d['description'] ?? ''),
            'sort_order'  => (int) ($d['sort_order'] ?? 0),
            'is_active'   => !empty($d['is_active']),
        ];
    }

    // ==================================================================
    // Looks (Mix & Match)
    // ==================================================================

    public function looksIndex(Request $request): void
    {
        Response::json(['looks' => Look::all()]);
    }

    public function lookShow(Request $request): void
    {
        $item = Look::find($this->idParam($request));
        $this->requireFound($item);
        Response::json($item);
    }

    public function lookStore(Request $request): void
    {
        $data = $this->validateLook($request->body());
        $id = Look::create($data);
        Response::json(Look::find($id), 201);
    }

    public function lookUpdate(Request $request): void
    {
        $id = $this->idParam($request);
        $this->requireFound(Look::find($id));
        $data = $this->validateLook($request->body());
        Look::update($id, $data);
        Response::json(Look::find($id));
    }

    public function lookDestroy(Request $request): void
    {
        $id = $this->idParam($request);
        $this->requireFound(Look::find($id));
        Look::delete($id);
        Response::json(['message' => 'Look deleted.']);
    }

    private function validateLook(array $d): array
    {
        $v = Validator::make($d)
            ->required('title')->max('title', 150)
            ->max('label', 50)
            ->max('pieces', 255)
            ->max('monogram', 8)
            ->max('image_url', 500)
            ->integer('sort_order');

        if ($v->fails()) {
            Response::error('The given data was invalid.', 422, $v->errors());
        }

        return [
            'label'       => (string) ($d['label'] ?? ''),
            'title'       => trim((string) $d['title']),
            'description' => (string) ($d['description'] ?? ''),
            'pieces'      => (string) ($d['pieces'] ?? ''),
            'monogram'   => $d['monogram'] ?? null,
            'image_url'  => $d['image_url'] ?? null,
            'sort_order' => (int) ($d['sort_order'] ?? 0),
            'is_active'  => !empty($d['is_active']),
        ];
    }

    // ==================================================================
    // News
    // ==================================================================

    public function newsIndex(Request $request): void
    {
        Response::json(['news' => NewsArticle::all()]);
    }

    public function newsShow(Request $request): void
    {
        $item = NewsArticle::find($this->idParam($request));
        $this->requireFound($item);
        Response::json($item);
    }

    public function newsStore(Request $request): void
    {
        $data = $this->validateNews($request->body());
        $id = NewsArticle::create($data);
        Response::json(NewsArticle::find($id), 201);
    }

    public function newsUpdate(Request $request): void
    {
        $id = $this->idParam($request);
        $this->requireFound(NewsArticle::find($id));
        $data = $this->validateNews($request->body(), $id);
        NewsArticle::update($id, $data);
        Response::json(NewsArticle::find($id));
    }

    public function newsDestroy(Request $request): void
    {
        $id = $this->idParam($request);
        $this->requireFound(NewsArticle::find($id));
        NewsArticle::delete($id);
        Response::json(['message' => 'Article deleted.']);
    }

    private function validateNews(array $d, ?int $excludeId = null): array
    {
        $v = Validator::make($d)
            ->required('title')->max('title', 200)
            ->required('excerpt')
            ->max('slug', 160)
            ->max('image_url', 500)
            ->date('published_at')
            ->integer('sort_order');

        if ($v->fails()) {
            Response::error('The given data was invalid.', 422, $v->errors());
        }

        // Auto-generate a slug from the title when none is supplied.
        $slug = trim((string) ($d['slug'] ?? ''));
        if ($slug === '') {
            $slug = $this->slugify((string) ($d['title'] ?? 'entry'));
        }

        // Guarantee slug uniqueness, appending -2, -3, ... as needed.
        $base = $slug;
        $i = 1;
        while (NewsArticle::slugExists($slug, $excludeId)) {
            $slug = $base . '-' . ++$i;
        }

        return [
            'slug'         => $slug,
            'title'       => trim((string) $d['title']),
            'excerpt'     => (string) ($d['excerpt'] ?? ''),
            'body'        => $d['body'] ?? null,
            'image_url'   => $d['image_url'] ?? null,
            'published_at' => $d['published_at'] ?? null,
            'sort_order'  => (int) ($d['sort_order'] ?? 0),
            'is_active'   => !empty($d['is_active']),
        ];
    }

    // ==================================================================
    // Home (settings + featured + stats + marquee)
    // ==================================================================

    public function homeShow(Request $request): void
    {
        Response::json([
            'settings' => HomeContent::settingsRow(),
            'featured' => HomeContent::featuredAll(),
            'stats'    => HomeContent::statsAll(),
            'marquee'  => HomeContent::marqueeAll(),
        ]);
    }

    public function homeSettingsUpdate(Request $request): void
    {
        $d = $request->body();
        $v = Validator::make($d)
            ->max('hero_eyebrow', 100)
            ->max('hero_title_primary', 120)
            ->max('hero_title_secondary', 120)
            ->max('hero_cta_label', 60)
            ->max('hero_cta_url', 255)
            ->max('philosophy_subtitle', 100)
            ->max('philosophy_title_primary', 120)
            ->max('philosophy_title_secondary', 120)
            ->max('philosophy_cta_label', 60)
            ->max('philosophy_cta_url', 255)
            ->max('philosophy_monogram', 8);

        if ($v->fails()) {
            Response::error('The given data was invalid.', 422, $v->errors());
        }

        HomeContent::updateSettings($d);
        Response::json(['settings' => HomeContent::settingsRow()]);
    }

    // ---- Featured ----

    public function homeFeaturedStore(Request $request): void
    {
        $data = $this->validateFeatured($request->body());
        $id = HomeContent::featuredCreate($data);
        Response::json(HomeContent::featuredFind($id), 201);
    }

    public function homeFeaturedUpdate(Request $request): void
    {
        $id = $this->idParam($request);
        $this->requireFound(HomeContent::featuredFind($id));
        $data = $this->validateFeatured($request->body());
        HomeContent::featuredUpdate($id, $data);
        Response::json(HomeContent::featuredFind($id));
    }

    public function homeFeaturedDestroy(Request $request): void
    {
        $id = $this->idParam($request);
        $this->requireFound(HomeContent::featuredFind($id));
        HomeContent::featuredDelete($id);
        Response::json(['message' => 'Featured card deleted.']);
    }

    private function validateFeatured(array $d): array
    {
        $v = Validator::make($d)
            ->max('tag', 80)
            ->max('title', 150)
            ->max('copy', 255)
            ->max('monogram', 8)
            ->max('image_url', 500)
            ->max('link_url', 255)
            ->integer('sort_order');

        if ($v->fails()) {
            Response::error('The given data was invalid.', 422, $v->errors());
        }

        return [
            'tag'        => (string) ($d['tag'] ?? ''),
            'title'      => (string) ($d['title'] ?? ''),
            'copy'       => (string) ($d['copy'] ?? ''),
            'monogram'   => $d['monogram'] ?? null,
            'image_url'  => $d['image_url'] ?? null,
            'link_url'   => (string) ($d['link_url'] ?? '/collection'),
            'sort_order' => (int) ($d['sort_order'] ?? 0),
            'is_active'  => !empty($d['is_active']),
        ];
    }

    // ---- Stats ----

    public function homeStatsStore(Request $request): void
    {
        $data = $this->validateStat($request->body());
        $id = HomeContent::statsCreate($data);
        Response::json(HomeContent::statsFind($id), 201);
    }

    public function homeStatsUpdate(Request $request): void
    {
        $id = $this->idParam($request);
        $this->requireFound(HomeContent::statsFind($id));
        $data = $this->validateStat($request->body());
        HomeContent::statsUpdate($id, $data);
        Response::json(HomeContent::statsFind($id));
    }

    public function homeStatsDestroy(Request $request): void
    {
        $id = $this->idParam($request);
        $this->requireFound(HomeContent::statsFind($id));
        HomeContent::statsDelete($id);
        Response::json(['message' => 'Stat deleted.']);
    }

    private function validateStat(array $d): array
    {
        $v = Validator::make($d)
            ->max('value', 20)
            ->max('suffix', 20)
            ->max('label', 100)
            ->integer('sort_order');

        if ($v->fails()) {
            Response::error('The given data was invalid.', 422, $v->errors());
        }

        return [
            'value'      => (string) ($d['value'] ?? ''),
            'suffix'     => (string) ($d['suffix'] ?? ''),
            'label'      => (string) ($d['label'] ?? ''),
            'sort_order' => (int) ($d['sort_order'] ?? 0),
            'is_active'  => !empty($d['is_active']),
        ];
    }

    // ---- Marquee ----

    public function homeMarqueeStore(Request $request): void
    {
        $data = $this->validateMarquee($request->body());
        $id = HomeContent::marqueeCreate($data);
        Response::json(HomeContent::marqueeFind($id), 201);
    }

    public function homeMarqueeUpdate(Request $request): void
    {
        $id = $this->idParam($request);
        $this->requireFound(HomeContent::marqueeFind($id));
        $data = $this->validateMarquee($request->body());
        HomeContent::marqueeUpdate($id, $data);
        Response::json(HomeContent::marqueeFind($id));
    }

    public function homeMarqueeDestroy(Request $request): void
    {
        $id = $this->idParam($request);
        $this->requireFound(HomeContent::marqueeFind($id));
        HomeContent::marqueeDelete($id);
        Response::json(['message' => 'Marquee word deleted.']);
    }

    private function validateMarquee(array $d): array
    {
        $v = Validator::make($d)
            ->required('word')->max('word', 120)
            ->integer('sort_order');

        if ($v->fails()) {
            Response::error('The given data was invalid.', 422, $v->errors());
        }

        return [
            'word'       => trim((string) $d['word']),
            'sort_order' => (int) ($d['sort_order'] ?? 0),
            'is_active'  => !empty($d['is_active']),
        ];
    }

    // ==================================================================
    // Shared helpers
    // ==================================================================

    /** Read and validate the {id} route parameter as an integer. */
    private function idParam(Request $request): int
    {
        $id = $request->param('id');
        if (!is_numeric($id)) {
            Response::error('Resource not found.', 404);
        }
        return (int) $id;
    }

    /** Abort with 404 when a model lookup returned null. */
    private function requireFound(?array $item): void
    {
        if ($item === null) {
            Response::error('Resource not found.', 404);
        }
    }

    /** Convert a title into a URL-friendly slug. */
    private function slugify(string $text): string
    {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = $slug !== null ? trim($slug, '-') : '';

        return $slug !== '' ? $slug : 'entry';
    }
}
