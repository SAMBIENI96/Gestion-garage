<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — AutoGest Pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-deep:    #080b10;
            --bg-panel:   #0e1118;
            --bg-input:   #13161f;
            --border:     #1e2333;
            --border-focus: #e8622a;
            --accent:     #e8622a;
            --accent-dim: rgba(232,98,42,0.15);
            --accent-glow:rgba(232,98,42,0.25);
            --text-1: #eceef5;
            --text-2: #7a8099;
            --text-3: #3d4357;
            --red:    #f04d4d;
            --star:   #4f7fff;
        }

        html, body {
            height: 100%;
            font-family: 'DM Sans', sans-serif;
            background: var(--bg-deep);
            color: var(--text-1);
        }

        /* ── Layout ── */
        .page {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        /* ── LEFT PANEL ── */
        .left {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 40px 52px;
            background: var(--bg-panel);
            position: relative;
            overflow: hidden;
        }

        /* Subtle noise texture overlay */
        .left::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            opacity: 0.4;
        }

        /* Brand */
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            z-index: 1;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            background: var(--accent);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-icon svg { width: 20px; height: 20px; fill: #fff; }

        .brand-name {
            font-family: 'Syne', sans-serif;
            font-size: 18px;
            font-weight: 800;
            color: var(--text-1);
            letter-spacing: -0.3px;
        }

        /* Form area */
        .form-area {
            position: relative;
            z-index: 1;
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
        }

        .form-label-top {
            font-size: 11px;
            font-weight: 600;
            color: var(--accent);
            letter-spacing: 2.5px;
            text-transform: uppercase;
            margin-bottom: 14px;
        }

        .form-title {
            font-family: 'Syne', sans-serif;
            font-size: 34px;
            font-weight: 800;
            color: var(--text-1);
            line-height: 1.15;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .form-subtitle {
            font-size: 14px;
            color: var(--text-2);
            line-height: 1.6;
            margin-bottom: 36px;
        }

        /* Error */
        .error-msg {
            background: rgba(240,77,77,0.07);
            border: 1px solid rgba(240,77,77,0.2);
            border-radius: 10px;
            padding: 11px 14px;
            color: var(--red);
            font-size: 13px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Form fields */
        .field { margin-bottom: 20px; }

        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-2);
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }

        .field-wrap {
            position: relative;
        }

        .field-wrap svg {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            stroke: var(--text-3);
            fill: none;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
            pointer-events: none;
            transition: stroke 0.2s;
        }

        .field-input {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 13px 42px 13px 16px;
            color: var(--text-1);
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            transition: border-color 0.18s, box-shadow 0.18s;
        }

        .field-input::placeholder { color: var(--text-3); }

        .field-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-dim);
        }

        .field-input:focus + svg { stroke: var(--accent); }

        /* Row */
        .field-row {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-bottom: 28px;
        }

        .forgot {
            font-size: 13px;
            color: var(--text-2);
            text-decoration: none;
            transition: color 0.15s;
        }
        .forgot:hover { color: var(--accent); }

        /* Button */
        .btn-submit {
            width: 100%;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            letter-spacing: 0.2px;
            transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
            position: relative;
            overflow: hidden;
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.08) 0%, transparent 100%);
        }

        .btn-submit:hover {
            background: #d4551f;
            box-shadow: 0 8px 24px rgba(232,98,42,0.35);
        }

        .btn-submit:active { transform: scale(0.99); }

        /* Footer */
        .left-footer {
            position: relative;
            z-index: 1;
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin-bottom: 20px;
        }

        .footer-copy {
            font-size: 12px;
            color: var(--text-3);
            text-align: center;
        }

        /* ── RIGHT PANEL ── */
        .right {
            position: relative;
            overflow: hidden;
        }

        .right-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.45);
        }

        /* Gradient overlay */
        .right::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to bottom,
                rgba(8,11,16,0.3) 0%,
                rgba(8,11,16,0.1) 40%,
                rgba(8,11,16,0.7) 80%,
                rgba(8,11,16,0.95) 100%
            );
            z-index: 1;
        }

        .right-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 40px 44px;
            z-index: 2;
        }

        .stars {
            display: flex;
            gap: 5px;
            margin-bottom: 16px;
        }

        .star {
            width: 18px;
            height: 18px;
            background: var(--star);
            clip-path: polygon(50% 0%,61% 35%,98% 35%,68% 57%,79% 91%,50% 70%,21% 91%,32% 57%,2% 35%,39% 35%);
        }

        .testimonial {
            font-family: 'Syne', sans-serif;
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            line-height: 1.4;
            margin-bottom: 22px;
            letter-spacing: -0.3px;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .author-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e8622a, #c44e1e);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 16px;
            color: #fff;
            flex-shrink: 0;
        }

        .author-name {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
        }

        .author-role {
            font-size: 12px;
            color: rgba(255,255,255,0.5);
            margin-top: 2px;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .page { grid-template-columns: 1fr; }
            .right { display: none; }
            .left { padding: 32px 28px; }
        }

        /* Animate in */
        .form-area > * {
            opacity: 0;
            transform: translateY(14px);
            animation: fadeUp 0.5s ease forwards;
        }
        .form-area > *:nth-child(1) { animation-delay: 0.05s; }
        .form-area > *:nth-child(2) { animation-delay: 0.12s; }
        .form-area > *:nth-child(3) { animation-delay: 0.18s; }
        .form-area > *:nth-child(4) { animation-delay: 0.24s; }
        .form-area > *:nth-child(5) { animation-delay: 0.30s; }
        .form-area > *:nth-child(6) { animation-delay: 0.36s; }

        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="page">

    <!-- ── LEFT ── -->
    <div class="left">

        <!-- Brand -->
        <div class="brand">
            <div class="brand-icon">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg>
            </div>
            <span class="brand-name">AutoGest Pro</span>
        </div>

        <!-- Form -->
        <div class="form-area">

            <div class="form-label-top">Admin Dashboard</div>

            <h1 class="form-title">Connexion à<br>votre espace</h1>

            <p class="form-subtitle">Gérez vos clients, pannes et réparations<br>en toute simplicité.</p>

            @if($errors->any())
                <div class="error-msg">
                    <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:none;stroke:#f04d4d;stroke-width:2;flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('error'))
                <div class="error-msg">{{ session('error') }}</div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="field">
                    <label class="field-label" for="email">Identifiant</label>
                    <div class="field-wrap">
                        <input class="field-input" type="email" id="email" name="email"
                               value="{{ old('email') }}" required autofocus
                               placeholder="Entrez votre identifiant">
                        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                </div>

                <div class="field">
                    <label class="field-label" for="password">Mot de passe</label>
                    <div class="field-wrap">
                        <input class="field-input" type="password" id="password" name="password"
                               required placeholder="Entrez votre mot de passe">
                        <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                </div>

                <div class="field-row">
                    <a href="#" class="forgot">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="btn-submit">Se connecter</button>
            </form>

        </div>

        <!-- Footer -->
        <div class="left-footer">
            <div class="divider"></div>
            <p class="footer-copy">© 2025 AutoGest Pro. Tous droits réservés.</p>
        </div>

    </div>

    <!-- ── RIGHT ── -->
    <div class="right">
        <img class="right-img"
             src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1200&q=80"
             alt="Garage automobile">

        <div class="right-content">
            <div class="stars">
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
            </div>

            <p class="testimonial">"L'outil indispensable pour piloter notre garage. Une vue claire sur chaque réparation en cours."</p>

            
        </div>
    </div>

</div>
</body>
</html>