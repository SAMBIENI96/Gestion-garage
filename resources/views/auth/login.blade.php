@extends('layouts.auth')
@section('title', 'Logiciel de gestion de garage · Connexion')
@section('path', '/login')

@section('content')
@if(\App\Support\Demo::enabled())
{{-- Invitation à la démo : le visiteur choisit de la voir, rien ne s'ouvre tout seul --}}
<section class="demo-invite" aria-labelledby="demo-title">
    <div class="demo-head">
        <span class="demo-ico"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg></span>
        <div class="grow">
            <h2 id="demo-title">Première visite ?</h2>
            <p>Découvrez l'application sans créer de compte.</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm demo-toggle" data-toggle="demo-roles" aria-expanded="false" aria-controls="demo-roles">
            <span>Voir la démo</span>
        </button>
    </div>
    <div class="demo-roles" id="demo-roles" hidden>
        <p class="demo-pick">Choisissez un rôle à découvrir · données fictives, remises à zéro chaque jour</p>
            @foreach([
                'patron'     => ['Patron', 'Tableau de bord, factures', 'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941'],
                'accueil'    => ['Accueil', 'Clients, réparations', 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
                'mecanicien' => ['Mécanicien', 'Appli mobile atelier', 'M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z'],
            ] as $role => [$label, $desc, $path])
            <form method="POST" action="{{ route('demo.login', $role) }}">
                @csrf
                <button type="submit" class="demo-role btn">
                    <span class="r-ico r-{{ $role }}"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg></span>
                    <span class="r-txt"><b>{{ $label }}</b><small>{{ $desc }}</small></span>
                </button>
            </form>
            @endforeach
        </div>
</section>
@endif

<span class="auth-eyebrow">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
    Espace garage
</span>

<div>
    <h1 class="auth-title">Bon retour parmi nous</h1>
    <p class="auth-sub">Connectez-vous pour suivre l'atelier, les clients et la facturation.</p>
</div>

<div>
    @if(session('status'))
        <div class="alert alert-info" role="status">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif
    @if($errors->any() || session('error'))
        <div class="alert alert-error" role="alert">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <span>{{ session('error') ?? $errors->first() }}</span>
        </div>
    @endif

    @include('partials.google-button')

    <form action="{{ route('login') }}" method="POST" novalidate>
        @csrf

        <div class="form-group">
            <label class="form-label" for="email">Adresse e-mail</label>
            <div class="input-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                <input class="form-input" type="email" id="email" name="email"
                       value="{{ old('email') }}" required autofocus
                       autocomplete="username" inputmode="email"
                       placeholder="nom@garage.bj"
                       @if($errors->has('email')) aria-invalid="true" @endif>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Mot de passe</label>
            <div class="input-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                <input class="form-input has-reveal" type="password" id="password" name="password"
                       required autocomplete="current-password" placeholder="••••••••"
                       @if($errors->has('email')) aria-invalid="true" @endif>
                @include('partials.reveal', ['for' => 'password'])
            </div>
        </div>

        <div class="auth-row">
            <label class="check-label">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                Se souvenir de moi
            </label>
            <button type="button" class="link-btn" data-toggle="forgot-hint" aria-expanded="false" aria-controls="forgot-hint">
                Mot de passe oublié ?
            </button>
        </div>

        <div id="forgot-hint" class="auth-hint" hidden>
            Demandez au patron du garage de réinitialiser votre mot de passe depuis la page <strong>Utilisateurs</strong>.
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block">
            <span>Se connecter</span>
        </button>
    </form>

    <p class="auth-switch">Pas encore de compte ? <a href="{{ route('register') }}">Demander un accès</a></p>
</div>

<div>
    <div class="auth-divider">Application atelier</div>
    <a href="{{ url('/mecanicien') }}" class="app-link">
        <span class="ico">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
        </span>
        <span>
            <strong>Vous êtes mécanicien ?</strong>
            <span>Ouvrez l'application mobile de l'atelier</span>
        </span>
        <svg class="arrow" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
    </a>
</div>
@endsection
