<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="color-scheme" content="light dark">
<meta name="theme-color" content="#ffffff">

{{-- Thème appliqué avant le premier rendu (pas de flash).
     Connecté : préférence du compte. Sinon (ou compte de démo partagé) : dernier choix sur cet appareil. --}}
<script>
(function () {
    var pref = @json(\App\Support\Demo::isDemoUser(auth()->user()) && \App\Support\Demo::enabled() ? null : auth()->user()?->theme);
    try {
        if (pref) localStorage.setItem('ag-theme', pref);
        else pref = localStorage.getItem('ag-theme');
    } catch (e) {}
    if (['light', 'dark', 'system'].indexOf(pref) < 0) pref = 'system';
    var dark = pref === 'dark' || (pref === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    var d = document.documentElement;
    d.dataset.theme = dark ? 'dark' : 'light';
    d.dataset.themePref = pref;
    if (dark) document.querySelector('meta[name="theme-color"]').content = '#10131a';
})();
</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/autogest.css') }}?v={{ filemtime(public_path('css/autogest.css')) }}">
<script src="{{ asset('js/autogest.js') }}?v={{ filemtime(public_path('js/autogest.js')) }}" defer></script>
