<?php

namespace Tests\Feature;

use Tests\TestCase;

// La page de connexion est la page d'entrée publique (référencement et aperçus de partage)
class SeoTest extends TestCase
{
    public function test_site_root_sends_visitors_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_login_page_is_indexable_with_share_preview(): void
    {
        config(['app.url' => 'https://app-nu-three-47.vercel.app']);

        $this->get('/login')->assertOk()
            ->assertSee('<link rel="canonical" href="https://app-nu-three-47.vercel.app/login">', false)
            ->assertSee('<meta property="og:image" content="https://app-nu-three-47.vercel.app/og-image.jpg">', false)
            ->assertSee('content="index, follow"', false);
    }

    public function test_robots_and_sitemap_point_to_the_login_page(): void
    {
        config(['app.url' => 'https://app-nu-three-47.vercel.app']);

        $this->get('/robots.txt')->assertOk()->assertSee('Allow: /login')->assertSee('Disallow: /')
            ->assertSee('Sitemap: https://app-nu-three-47.vercel.app/sitemap.xml');
        $this->get('/sitemap.xml')->assertOk()->assertSee('<loc>https://app-nu-three-47.vercel.app/login</loc>', false);
    }
}
