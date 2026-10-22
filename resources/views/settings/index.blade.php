@extends('layouts.app')
@section('title', 'Paramètres')
@section('page-title', 'Paramètres')

@php($check = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>')

@section('content')
<div class="settings">

    <nav class="settings-nav" aria-label="Sections des paramètres">
        <a href="#apparence" class="active">Apparence</a>
        <a href="#profil">Profil</a>
        <a href="#securite">Mot de passe</a>
    </nav>

    <div>
        {{-- ── Apparence ─────────────────────────────────────────── --}}
        <section id="apparence" class="card settings-section">
            <h2 class="card-title">Apparence</h2>
            <p class="card-sub">Choisissez le thème de l'interface. Le choix est enregistré sur votre compte et s'applique sur tous vos appareils.</p>

            <form method="POST" action="{{ route('settings.theme') }}" data-theme-form>
                @csrf
                @method('PATCH')
                <div class="theme-options" role="radiogroup" aria-label="Thème">
                    <label class="theme-option">
                        <input type="radio" name="theme" value="light" @checked($user->theme === 'light')>
                        <span class="preview">
                            <span class="mock light">
                                <span class="mock-side"><i></i><i></i><i></i><i></i></span>
                                <span class="mock-main"><span class="bar"></span><span class="row"><b></b><b></b></span></span>
                            </span>
                        </span>
                        <span class="check">{!! $check !!}</span>
                        <span class="label">Clair <span class="desc">Lumineux, idéal en journée</span></span>
                    </label>

                    <label class="theme-option">
                        <input type="radio" name="theme" value="dark" @checked($user->theme === 'dark')>
                        <span class="preview">
                            <span class="mock dark">
                                <span class="mock-side"><i></i><i></i><i></i><i></i></span>
                                <span class="mock-main"><span class="bar"></span><span class="row"><b></b><b></b></span></span>
                            </span>
                        </span>
                        <span class="check">{!! $check !!}</span>
                        <span class="label">Sombre <span class="desc">Repose les yeux</span></span>
                    </label>

                    <label class="theme-option">
                        <input type="radio" name="theme" value="system" @checked(! in_array($user->theme, ['light', 'dark']))>
                        <span class="preview">
                            <span class="mock-split">
                                <span class="mock light">
                                    <span class="mock-side"><i></i><i></i><i></i><i></i></span>
                                    <span class="mock-main"><span class="bar"></span><span class="row"><b></b><b></b></span></span>
                                </span>
                                <span class="mock dark">
                                    <span class="mock-side"><i></i><i></i><i></i><i></i></span>
                                    <span class="mock-main"><span class="bar"></span><span class="row"><b></b><b></b></span></span>
                                </span>
                            </span>
                        </span>
                        <span class="check">{!! $check !!}</span>
                        <span class="label">Système <span class="desc">Suit votre appareil</span></span>
                    </label>
                </div>

                {{-- Sans JavaScript, le choix s'enregistre avec ce bouton --}}
                <noscript>
                    <div class="settings-footer"><button type="submit" class="btn btn-primary">Enregistrer</button></div>
                </noscript>
            </form>
        </section>

        {{-- ── Profil ────────────────────────────────────────────── --}}
        <section id="profil" class="card settings-section">
            <h2 class="card-title">Profil</h2>
            <p class="card-sub">Vos informations visibles par l'équipe.</p>

            <div class="profile-head">
                <span class="avatar lg" aria-hidden="true">{{ $user->initiales }}</span>
                <div>
                    <div class="name">{{ $user->name }}</div>
                    <div class="sub">{{ $user->role_label }} · membre depuis {{ $user->created_at?->translatedFormat('F Y') ?? '—' }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('settings.profile') }}">
                @csrf
                @method('PATCH')
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="name">Nom complet</label>
                        <input class="form-input" id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" @error('name') aria-invalid="true" @enderror>
                        @error('name')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="phone">Téléphone</label>
                        <input class="form-input" id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel" placeholder="+229 …">
                        @error('phone')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Adresse e-mail</label>
                    <input class="form-input" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email" @error('email') aria-invalid="true" @enderror>
                    <div class="form-hint">Sert d'identifiant de connexion.</div>
                    @error('email')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="settings-footer">
                    <button type="submit" class="btn btn-primary"><span>Enregistrer le profil</span></button>
                </div>
            </form>
        </section>

        {{-- ── Mot de passe ──────────────────────────────────────── --}}
        <section id="securite" class="card settings-section">
            <h2 class="card-title">Mot de passe</h2>
            @if($user->password)
                <p class="card-sub">Au moins 8 caractères, avec des lettres et des chiffres.</p>
            @else
                <p class="card-sub">Vous vous connectez avec Google. Définissez un mot de passe pour pouvoir aussi vous connecter avec votre e-mail (et à l'application mécanicien).</p>
            @endif

            <form method="POST" action="{{ route('settings.password') }}">
                @csrf
                @method('PUT')
                @if($user->password)
                <div class="form-group">
                    <label class="form-label" for="current_password">Mot de passe actuel</label>
                    <div class="input-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        <input class="form-input has-reveal" id="current_password" name="current_password" type="password" required autocomplete="current-password" @error('current_password') aria-invalid="true" @enderror>
                        @include('partials.reveal', ['for' => 'current_password'])
                    </div>
                    @error('current_password')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                @endif
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="password">Nouveau mot de passe</label>
                        <div class="input-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                            <input class="form-input has-reveal" id="password" name="password" type="password" required minlength="8" autocomplete="new-password" @error('password') aria-invalid="true" @enderror>
                            @include('partials.reveal', ['for' => 'password'])
                        </div>
                        @error('password')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirmation</label>
                        <div class="input-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            <input class="form-input has-reveal" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                            @include('partials.reveal', ['for' => 'password_confirmation'])
                        </div>
                    </div>
                </div>
                <div class="settings-footer">
                    <button type="submit" class="btn btn-primary"><span>Changer le mot de passe</span></button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
