@extends('layouts.auth')
@section('title', 'Demander un accès')
@section('path', '/inscription')

@section('content')
<span class="auth-eyebrow">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
    Nouveau membre
</span>

<div>
    <h1 class="auth-title">Rejoindre l'équipe</h1>
    <p class="auth-sub">Créez votre compte. Le patron du garage le validera avant votre première connexion.</p>
</div>

<div>
    @include('partials.google-button', ['label' => "S'inscrire avec Google"])

    <form action="{{ route('register') }}" method="POST" novalidate>
        @csrf

        <div class="form-group">
            <span class="form-label" id="role-label">Je travaille à</span>
            <div class="segmented" role="radiogroup" aria-labelledby="role-label">
                <label>
                    <input type="radio" name="role" value="accueil" @checked(old('role', 'accueil') === 'accueil')>
                    <span>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/></svg>
                        L'accueil
                    </span>
                </label>
                <label>
                    <input type="radio" name="role" value="mecanicien" @checked(old('role') === 'mecanicien')>
                    <span>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z"/></svg>
                        L'atelier
                    </span>
                </label>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label" for="name">Nom complet</label>
                <input class="form-input" id="name" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Kofi Mensah" @error('name') aria-invalid="true" @enderror>
                @error('name')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="phone">Téléphone <span style="color:var(--text-muted)">(facultatif)</span></label>
                <input class="form-input" id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" placeholder="+229 …">
                @error('phone')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Adresse e-mail</label>
            <div class="input-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                <input class="form-input" type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" inputmode="email" placeholder="nom@garage.bj" @error('email') aria-invalid="true" @enderror>
            </div>
            @error('email')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label" for="password">Mot de passe</label>
                <div class="input-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    <input class="form-input has-reveal" type="password" id="password" name="password" required minlength="8" autocomplete="new-password" @error('password') aria-invalid="true" @enderror>
                    @include('partials.reveal', ['for' => 'password'])
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirmation</label>
                <div class="input-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    <input class="form-input has-reveal" type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                    @include('partials.reveal', ['for' => 'password_confirmation'])
                </div>
            </div>
        </div>
        @error('password')<div class="form-error" style="margin:-10px 0 14px">{{ $message }}</div>@enderror
        <p class="form-hint" style="margin:-8px 0 20px">Au moins 8 caractères, avec des lettres et des chiffres.</p>

        <button type="submit" class="btn btn-primary btn-lg btn-block">
            <span>Envoyer ma demande</span>
        </button>
    </form>

    <p class="auth-switch">Déjà un compte ? <a href="{{ route('login') }}">Se connecter</a></p>
</div>
@endsection
