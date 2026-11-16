<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sitemap generation
    |--------------------------------------------------------------------------
    |
    | Stay below the protocol limit of 50,000 URLs per child sitemap. The
    | default leaves headroom for future fixed entries and can be tuned per
    | installation without editing application code.
    |
    */
    'sitemap_page_size' => max(1, min(50000, (int) env('SEO_SITEMAP_PAGE_SIZE', 45000))),
];
