<!DOCTYPE html>
<html lang="fr">
<head>
    @include('partials.head')
    @php($site = \App\Support\Seo::baseUrl())
    @php($description = "AutoGest Pro, le logiciel de gestion de garage automobile : clients, ordres de réparation, planning de l'atelier, facturation et application mobile pour les mécaniciens. Démo gratuite, sans inscription.")
    <title>@yield('title') — AutoGest Pro</title>

    {{-- Référencement : la page de connexion est la page d'entrée du site --}}
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ $site }}@yield('path', '/login')">
    <meta name="robots" content="index, follow">

    {{-- Aperçu lors d'un partage (LinkedIn, WhatsApp, Facebook, X) --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="AutoGest Pro">
    <meta property="og:title" content="AutoGest Pro — Gestion de garage automobile">
    <meta property="og:description" content="Clients, réparations, planning, factures et appli mécanicien. Essayez la démo gratuite, sans inscription.">
    <meta property="og:url" content="{{ $site }}@yield('path', '/login')">
    <meta property="og:image" content="{{ $site }}/og-image.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="AutoGest Pro — Essayez la démo gratuite">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="AutoGest Pro — Gestion de garage automobile">
    <meta name="twitter:description" content="Clients, réparations, planning, factures et appli mécanicien. Démo gratuite, sans inscription.">
    <meta name="twitter:image" content="{{ $site }}/og-image.jpg">
</head>
<body>
<div class="auth">

    <!-- ── Formulaire ── -->
    <main class="auth-main">
        <div class="auth-top">
            <a href="{{ route('login') }}" class="sidebar-logo" style="padding:0;height:auto">
                <span class="logo-mark">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z"/></svg>
                </span>
                <span class="brand">Auto<span>Gest</span> Pro</span>
            </a>
            <button type="button" class="icon-btn theme-toggle" data-theme-toggle aria-label="Basculer entre thème clair et sombre" title="Changer de thème">
                <svg class="i-moon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
                <svg class="i-sun" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
            </button>
        </div>

        <div class="auth-form-wrap">
            @yield('content')
        </div>

        <p class="auth-foot">© {{ date('Y') }} AutoGest Pro</p>
    </main>

    <!-- ── Visuel ── -->
    <aside class="auth-visual" aria-hidden="true">
        <img class="visual-img"
             src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1200&q=80"
             alt="" loading="eager" decoding="async">

        <div class="visual-copy">
            <h2>Tout l'atelier, en un coup d'œil. Du diagnostic à la facture.</h2>
            <ul>
                <li>Suivi des réparations</li>
                <li>Factures en un clic</li>
                <li>App mobile mécanicien</li>
            </ul>
        </div>
    </aside>

</div>
</body>
</html>
