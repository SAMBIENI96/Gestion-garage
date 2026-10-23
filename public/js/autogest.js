/* AutoGest Pro — interactions de l'interface (aucune dépendance). */
(function () {
    'use strict';

    var root = document.documentElement;
    var STORAGE_KEY = 'ag-theme';
    var media = window.matchMedia('(prefers-color-scheme: dark)');
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    function csrf() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.content : '';
    }

    function store(pref) {
        try { localStorage.setItem(STORAGE_KEY, pref); } catch (e) { /* stockage indisponible */ }
    }

    /* ── Thème ─────────────────────────────────────────────────────── */
    function resolve(pref) {
        return pref === 'dark' || (pref === 'system' && media.matches) ? 'dark' : 'light';
    }

    function setTheme(pref, origin) {
        var next = resolve(pref);
        var update = function () {
            root.dataset.theme = next;
            root.dataset.themePref = pref;
            var meta = document.querySelector('meta[name="theme-color"]');
            if (meta) meta.content = next === 'dark' ? '#10131a' : '#ffffff';
        };
        store(pref);

        if (root.dataset.theme === next || reduceMotion.matches) { update(); return; }

        // Révélation circulaire depuis le point de clic (navigateurs compatibles)
        if (document.startViewTransition && origin) {
            var x = origin.x, y = origin.y;
            var r = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));
            document.startViewTransition(update).ready.then(function () {
                root.animate(
                    { clipPath: ['circle(0px at ' + x + 'px ' + y + 'px)', 'circle(' + r + 'px at ' + x + 'px ' + y + 'px)'] },
                    { duration: 520, easing: 'cubic-bezier(.16,1,.3,1)', pseudoElement: '::view-transition-new(root)' }
                );
            });
            return;
        }

        root.classList.add('theme-anim');
        update();
        setTimeout(function () { root.classList.remove('theme-anim'); }, 400);
    }

    function saveTheme(pref) {
        var endpoint = document.querySelector('meta[name="theme-endpoint"]');
        if (!endpoint) return Promise.resolve();
        return fetch(endpoint.content, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            body: JSON.stringify({ theme: pref }),
            credentials: 'same-origin'
        }).then(function (res) {
            if (!res.ok) throw new Error(res.status);
        });
    }

    function originOf(el, evt) {
        if (evt && evt.clientX) return { x: evt.clientX, y: evt.clientY };
        var r = el.getBoundingClientRect();
        return { x: r.left + r.width / 2, y: r.top + r.height / 2 };
    }

    var LABELS = { light: 'Thème clair', dark: 'Thème sombre', system: 'Thème du système' };

    // Suivre le système en direct quand la préférence est « system »
    media.addEventListener('change', function () {
        if ((root.dataset.themePref || 'system') === 'system') setTheme('system');
    });

    // Bouton rapide (barre du haut, page de connexion)
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-theme-toggle]');
        if (!btn) return;
        var pref = root.dataset.theme === 'dark' ? 'light' : 'dark';
        setTheme(pref, originOf(btn, e));
        syncRadios(pref);
        saveTheme(pref).catch(function () { toast("Impossible d'enregistrer le thème.", 'error'); });
    });

    // Choix dans la page Paramètres
    function syncRadios(pref) {
        document.querySelectorAll('input[name="theme"]').forEach(function (r) { r.checked = r.value === pref; });
    }
    document.addEventListener('change', function (e) {
        var input = e.target;
        if (input.name !== 'theme' || !input.closest('[data-theme-form]')) return;
        setTheme(input.value, originOf(input.closest('label') || input));
        saveTheme(input.value)
            .then(function () { toast(LABELS[input.value] + ' enregistré.', 'success'); })
            .catch(function () { toast("Impossible d'enregistrer le thème.", 'error'); });
    });

    // Coche le thème réellement appliqué (utile quand il vient de l'appareil)
    syncRadios(root.dataset.themePref || 'system');

    /* ── Notifications ─────────────────────────────────────────────── */
    var ICONS = {
        success: '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>',
        error:   '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>',
        info:    '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v.01M12 11v5"/>'
    };

    function toast(message, type) {
        type = ICONS[type] ? type : 'info';
        var box = document.querySelector('.toasts');
        if (!box) {
            box = document.createElement('div');
            box.className = 'toasts';
            box.setAttribute('aria-live', 'polite');
            document.body.appendChild(box);
        }
        var el = document.createElement('div');
        el.className = 'toast ' + type;
        el.setAttribute('role', type === 'error' ? 'alert' : 'status');
        el.innerHTML =
            '<span class="t-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6">' + ICONS[type] + '</svg></span>' +
            '<div class="t-body"></div>' +
            '<button type="button" class="t-close" aria-label="Fermer"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg></button>';
        el.querySelector('.t-body').textContent = message;

        var timer;
        var close = function () {
            clearTimeout(timer);
            el.classList.add('leaving');
            el.addEventListener('animationend', function () { el.remove(); }, { once: true });
        };
        var arm = function () { timer = setTimeout(close, type === 'error' ? 7000 : 4500); };
        el.querySelector('.t-close').addEventListener('click', close);
        el.addEventListener('mouseenter', function () { clearTimeout(timer); });
        el.addEventListener('mouseleave', arm);
        box.appendChild(el);
        arm();
    }
    window.toast = toast;

    /* ── Menu latéral mobile ───────────────────────────────────────── */
    var body = document.body;
    function setNav(open) {
        body.classList.toggle('nav-open', open);
        var btn = document.getElementById('menu-btn');
        if (btn) btn.setAttribute('aria-expanded', String(open));
    }
    document.addEventListener('click', function (e) {
        if (e.target.closest('#menu-btn')) { setNav(!body.classList.contains('nav-open')); return; }
        if (e.target.closest('.scrim') || e.target.closest('#sidebar a')) setNav(false);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && body.classList.contains('nav-open')) setNav(false);
    });

    /* ── Formulaires : état de chargement et anti double-envoi ─────── */
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (e.defaultPrevented || form.hasAttribute('data-no-loading') || form.target === '_blank') return;
        if (form.dataset.busy) { e.preventDefault(); return; }
        form.dataset.busy = '1';
        // Ne pas désactiver le bouton : sa valeur (name/value) doit partir avec le formulaire
        var btn = e.submitter || form.querySelector('[type="submit"]');
        if (btn && btn.classList.contains('btn')) {
            btn.classList.add('is-loading');
            btn.setAttribute('aria-busy', 'true');
        }
    });
    // Retour arrière (cache navigateur) : réarmer les formulaires
    window.addEventListener('pageshow', function () {
        document.querySelectorAll('form[data-busy]').forEach(function (f) { delete f.dataset.busy; });
        document.querySelectorAll('.btn.is-loading').forEach(function (b) {
            b.classList.remove('is-loading');
            b.removeAttribute('aria-busy');
        });
    });

    /* ── Afficher / masquer un mot de passe ────────────────────────── */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-reveal]');
        if (!btn) return;
        var input = document.getElementById(btn.dataset.reveal);
        var shown = input.type === 'password';
        input.type = shown ? 'text' : 'password';
        btn.setAttribute('aria-pressed', String(shown));
        btn.setAttribute('aria-label', shown ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        input.focus();
    });

    /* ── Bloc d'aide repliable ─────────────────────────────────────── */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-toggle]');
        if (!btn) return;
        var target = document.getElementById(btn.dataset.toggle);
        var open = target.hidden;
        target.hidden = !open;
        btn.setAttribute('aria-expanded', String(open));
        if (btn.classList.contains('demo-toggle')) btn.firstElementChild.textContent = open ? 'Masquer' : 'Voir la démo';
    });

    /* ── Messages flash du serveur → notifications ─────────────────── */
    document.querySelectorAll('[data-flash]').forEach(function (el) {
        toast(el.dataset.flash, el.dataset.type);
        el.remove();
    });

    /* ── Navigation de la page Paramètres (section active) ─────────── */
    var links = document.querySelectorAll('.settings-nav a');
    if (links.length && 'IntersectionObserver' in window) {
        var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (!en.isIntersecting) return;
                links.forEach(function (a) { a.classList.toggle('active', a.hash === '#' + en.target.id); });
            });
        }, { rootMargin: '-30% 0px -60% 0px' });
        document.querySelectorAll('.settings-section').forEach(function (s) { obs.observe(s); });
    }
})();
