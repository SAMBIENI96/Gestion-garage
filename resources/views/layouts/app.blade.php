<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Garage') — AutoGest Pro</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-base:    #0d0f14;
            --bg-surface: #151820;
            --bg-card:    #1c2030;
            --bg-hover:   #222840;
            --border:     #2a3045;
            --border-mid: #3a4060;
            --accent:     #e8622a;
            --accent-dim: #c44e1e;
            --accent-glow:rgba(232,98,42,0.15);
            --blue:       #4a9eff;
            --green:      #3ecf8e;
            --yellow:     #f5c842;
            --red:        #f04d4d;
            --text-primary:   #e8eaf2;
            --text-secondary: #8b91a8;
            --text-muted:     #555d78;
            --radius:     10px;
            --radius-lg:  16px;
            --sidebar-w:  240px;
            --font-display: 'Syne', sans-serif;
            --font-body:    'DM Sans', sans-serif;
        }

        html, body { height: 100%; }

        body {
            background: var(--bg-base);
            color: var(--text-primary);
            font-family: var(--font-body);
            font-size: 14px;
            line-height: 1.6;
            display: flex;
        }

        /* ── Sidebar ─────────────────────────────────────── */
        #sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: var(--bg-surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            transition: transform 0.25s ease;
        }

        .sidebar-logo {
            padding: 24px 20px 20px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-logo .brand {
            font-family: var(--font-display);
            font-size: 20px;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.5px;
        }

        .sidebar-logo .brand span {
            color: var(--accent);
        }

        .sidebar-logo .tagline {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .sidebar-user {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-user .user-name {
            font-weight: 500;
            font-size: 13px;
            color: var(--text-primary);
        }

        .sidebar-user .user-role {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .sidebar-user .role-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .role-badge.patron    { background: rgba(232,98,42,0.2); color: var(--accent); }
        .role-badge.accueil   { background: rgba(74,158,255,0.15); color: var(--blue); }
        .role-badge.mecanicien{ background: rgba(62,207,142,0.15); color: var(--green); }

        nav.sidebar-nav {
            flex: 1;
            padding: 12px 0;
            overflow-y: auto;
        }

        .nav-section {
            padding: 8px 20px 4px;
            font-size: 10px;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 20px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 400;
            transition: all 0.15s;
            border-left: 2px solid transparent;
            position: relative;
        }

        .nav-item:hover {
            color: var(--text-primary);
            background: var(--bg-hover);
        }

        .nav-item.active {
            color: var(--text-primary);
            background: var(--accent-glow);
            border-left-color: var(--accent);
            font-weight: 500;
        }

        .nav-item .nav-icon {
            width: 18px;
            height: 18px;
            opacity: 0.7;
            flex-shrink: 0;
        }

        .nav-item.active .nav-icon { opacity: 1; }

        .nav-badge {
            margin-left: auto;
            background: var(--accent);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--border);
        }

        /* ── Main content ─────────────────────────────────── */
        #main {
            margin-left: var(--sidebar-w);
            flex: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Top bar ─────────────────────────────────────── */
        #topbar {
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border);
            padding: 0 28px;
            height: 56px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-title {
            font-family: var(--font-display);
            font-size: 17px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .topbar-actions { margin-left: auto; display: flex; align-items: center; gap: 10px; }

        /* ── Page content ─────────────────────────────────── */
        #content { padding: 28px; flex: 1; }

        /* ── Components ───────────────────────────────────── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px;
        }

        .card-title {
            font-family: var(--font-display);
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 16px;
        }

        /* Stat cards */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: border-color 0.2s;
        }

        .stat-card:hover { border-color: var(--border-mid); }

        .stat-card .stat-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
        }

        .stat-card .stat-value {
            font-family: var(--font-display);
            font-size: 32px;
            font-weight: 800;
            color: var(--text-primary);
            margin: 6px 0 0;
            line-height: 1;
        }

        .stat-card .stat-icon {
            position: absolute;
            top: 16px; right: 16px;
            width: 36px; height: 36px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: var(--radius);
            font-family: var(--font-body);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.15s;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
        }
        .btn-primary:hover { background: var(--accent-dim); }

        .btn-secondary {
            background: var(--bg-hover);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }
        .btn-secondary:hover { border-color: var(--border-mid); }

        .btn-ghost {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border);
        }
        .btn-ghost:hover { color: var(--text-primary); border-color: var(--border-mid); }

        .btn-danger {
            background: rgba(240,77,77,0.15);
            color: var(--red);
            border: 1px solid rgba(240,77,77,0.3);
        }
        .btn-danger:hover { background: rgba(240,77,77,0.25); }

        .btn-sm { padding: 5px 12px; font-size: 12px; }

        /* Tables */
        .table-wrap { overflow-x: auto; }

        table.tbl {
            width: 100%;
            border-collapse: collapse;
        }

        table.tbl th {
            text-align: left;
            padding: 10px 14px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.7px;
            border-bottom: 1px solid var(--border);
        }

        table.tbl td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--border);
            color: var(--text-secondary);
            font-size: 13.5px;
        }

        table.tbl tr:hover td { background: var(--bg-hover); }
        table.tbl tr:last-child td { border-bottom: none; }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-blue   { background: rgba(74,158,255,0.15); color: var(--blue); }
        .badge-green  { background: rgba(62,207,142,0.15); color: var(--green); }
        .badge-yellow { background: rgba(245,200,66,0.15); color: var(--yellow); }
        .badge-red    { background: rgba(240,77,77,0.15);  color: var(--red); }
        .badge-orange { background: rgba(232,98,42,0.2);   color: var(--accent); }
        .badge-gray   { background: rgba(139,145,168,0.1); color: var(--text-muted); }

        /* Forms */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 9px 12px;
            color: var(--text-primary);
            font-family: var(--font-body);
            font-size: 14px;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .form-select option { background: var(--bg-surface); }
        .form-textarea { resize: vertical; min-height: 80px; }

        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius);
            font-size: 13.5px;
            margin-bottom: 16px;
            border-left: 3px solid;
        }
        .alert-success { background: rgba(62,207,142,0.08); border-color: var(--green); color: var(--green); }
        .alert-error   { background: rgba(240,77,77,0.08);  border-color: var(--red);   color: var(--red); }
        .alert-info    { background: rgba(74,158,255,0.08); border-color: var(--blue);  color: var(--blue); }

        /* Page header */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            gap: 16px;
        }

        .page-header h1 {
            font-family: var(--font-display);
            font-size: 24px;
            font-weight: 800;
            color: var(--text-primary);
        }

        .page-header .breadcrumb {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Urgence dot */
        .urgence-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }
        .urgence-dot.vip    { background: #f5c842; box-shadow: 0 0 6px #f5c842; }
        .urgence-dot.urgent { background: var(--red); box-shadow: 0 0 6px var(--red); }
        .urgence-dot.normal { background: var(--text-muted); }

        /* Pagination */
        .pagination { display: flex; gap: 4px; align-items: center; margin-top: 20px; }
        .pagination a, .pagination span {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            color: var(--text-secondary);
            text-decoration: none;
            border: 1px solid var(--border);
        }
        .pagination a:hover { background: var(--bg-hover); color: var(--text-primary); }
        .pagination .active { background: var(--accent); color: #fff; border-color: var(--accent); }

        /* Logout form */
        .logout-form { display: inline; }
        .logout-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 13.5px;
            font-family: var(--font-body);
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 0;
            transition: color 0.15s;
            width: 100%;
        }
        .logout-btn:hover { color: var(--red); }

        /* Responsive */
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #main { margin-left: 0; }
            .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; }
        }
    </style>

    @stack('styles')
</head>
<body>

<!-- ══ SIDEBAR ══════════════════════════════════════════════════ -->
<aside id="sidebar">
    <div class="sidebar-logo">
        <div class="brand">Auto<span>Gest</span></div>
        <div class="tagline">Gestion de garage</div>
    </div>

    <div class="sidebar-user">
        <div class="user-name">{{ auth()->user()->name }}</div>
        <div><span class="role-badge {{ auth()->user()->role }}">{{ auth()->user()->role_label }}</span></div>
    </div>

    <nav class="sidebar-nav">
        @if(auth()->user()->isPatron() || auth()->user()->isAccueil())
        <div class="nav-section">Principal</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Tableau de bord
        </a>
        @endif

        @if(auth()->user()->isMecanicien())
        <div class="nav-section">Mes tâches</div>
        <a href="{{ route('repairs.mecanicien') }}" class="nav-item {{ request()->routeIs('repairs.mecanicien') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
            </svg>
            Mes interventions
        </a>
        @endif

        @if(auth()->user()->isPatron() || auth()->user()->isAccueil())
        <div class="nav-section">Gestion</div>
        <a href="{{ route('clients.index') }}" class="nav-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
            </svg>
            Clients
        </a>
        <a href="{{ route('vehicles.index') }}" class="nav-item {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
            </svg>
            Véhicules
        </a>
        <a href="{{ route('repairs.index') }}" class="nav-item {{ request()->routeIs('repairs.index', 'repairs.show', 'repairs.create', 'repairs.edit') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
            </svg>
            Ordres de réparation
        </a>
        @endif

        @if(auth()->user()->isPatron())
        <a href="{{ route('planning.index') }}" class="nav-item {{ request()->routeIs('planning.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
            </svg>
            Planning atelier
        </a>

        <div class="nav-section">Finance</div>
        <a href="{{ route('invoices.index') }}" class="nav-item {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
            Factures
        </a>

        <div class="nav-section">Administration</div>
        <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
            </svg>
            Utilisateurs
        </a>
        @endif
    </nav>

    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST" class="logout-form">
            @csrf
            <button type="submit" class="logout-btn">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                </svg>
                Déconnexion
            </button>
        </form>
    </div>
</aside>

<!-- ══ MAIN ══════════════════════════════════════════════════════ -->
<div id="main">
    <header id="topbar">
        <div class="topbar-title">@yield('page-title', 'Tableau de bord')</div>
        <div class="topbar-actions">
            @stack('topbar-actions')
        </div>
    </header>

    <div id="content">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">
                <ul style="list-style:none;">
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</div>

@stack('scripts')
</body>
</html>
