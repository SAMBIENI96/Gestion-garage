<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — AutoGest Pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg-base:    #0d0f14;
            --bg-surface: #151820;
            --bg-card:    #1c2030;
            --border:     #2a3045;
            --accent:     #e8622a;
            --accent-glow:rgba(232,98,42,0.2);
            --text-primary:   #e8eaf2;
            --text-secondary: #8b91a8;
            --text-muted:     #555d78;
            --red:        #f04d4d;
        }
        body {
            min-height: 100vh;
            background: var(--bg-base);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'DM Sans', sans-serif;
        }

        /* Subtle background grid */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(42,48,69,0.4) 1px, transparent 1px),
                linear-gradient(90deg, rgba(42,48,69,0.4) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .login-wrap {
            width: 100%;
            max-width: 420px;
            padding: 24px;
            position: relative;
            z-index: 1;
        }

        .login-header {
            text-align: center;
            margin-bottom: 36px;
        }

        .brand {
            font-family: 'Syne', sans-serif;
            font-size: 36px;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -1px;
        }
        .brand span { color: var(--accent); }

        .tagline {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px;
        }

        .login-card h2 {
            font-family: 'Syne', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .login-card p {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 28px;
        }

        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 7px;
        }

        .form-input {
            width: 100%;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 11px 14px;
            color: var(--text-primary);
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }
        .form-input::placeholder { color: var(--text-muted); }

        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-secondary);
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
        }
        .btn-login:hover { background: #c44e1e; }
        .btn-login:active { transform: scale(0.99); }

        .error-msg {
            background: rgba(240,77,77,0.08);
            border: 1px solid rgba(240,77,77,0.25);
            border-radius: 10px;
            padding: 10px 14px;
            color: var(--red);
            font-size: 13px;
            margin-bottom: 20px;
        }

        .footer-note {
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 24px;
        }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-header">
        <div class="brand">Auto<span>Gest</span></div>
        <div class="tagline">Système de gestion garage</div>
    </div>

    <div class="login-card">
        <h2>Bienvenue</h2>
        <p>Connectez-vous à votre espace de travail</p>

        @if($errors->any())
            <div class="error-msg">{{ $errors->first() }}</div>
        @endif

        @if(session('error'))
            <div class="error-msg">{{ session('error') }}</div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Adresse email</label>
                <input class="form-input" type="email" id="email" name="email"
                       value="{{ old('email') }}" required autofocus
                       placeholder="exemple@garage.com">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Mot de passe</label>
                <input class="form-input" type="password" id="password" name="password"
                       required placeholder="••••••••">
            </div>
            <div class="form-row">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" style="accent-color: var(--accent);">
                    Se souvenir de moi
                </label>
            </div>
            <button type="submit" class="btn-login">Se connecter</button>
        </form>
    </div>

    <div class="footer-note">AutoGest Pro v1.0 — Gestion interne garage</div>
</div>
</body>
</html>
