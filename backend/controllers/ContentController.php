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

/**
 * Public, read-only content endpoints consumed by the marketing pages.
 */
class ContentController
{
    /** GET /api/content/home */
    public function home(Request $request): void
    {
        Response::json(HomeContent::payload());
    }

    /** GET /api/collections */
    public function collections(Request $request): void
    {
        Response::json(['collections' => Collection::allActive()]);
    }

    /** GET /api/guidelines */
    public function guidelines(Request $request): void
    {
        Response::json(['guidelines' => Guideline::allActive()]);
    }

    /** GET /api/mix-match */
    public function mixMatch(Request $request): void
    {
        Response::json(['looks' => Look::allActive()]);
    }

    /** GET /api/news */
    public function news(Request $request): void
    {
        Response::json(['news' => NewsArticle::allActive()]);
    }
}
