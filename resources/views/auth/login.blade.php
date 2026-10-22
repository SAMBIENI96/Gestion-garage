@extends('layouts.auth')
@section('title', 'Connexion')

@section('content')
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
