<?php

namespace App\Support;

class Seo
{
    /**
     * Adresse publique du site pour les liens absolus (canonical, aperçus, sitemap) :
     * APP_URL si elle est renseignée, sinon l'adresse de la requête en cours.
     */
    public static function baseUrl(): string
    {
        $configured = rtrim((string) config('app.url'), '/');

        return $configured !== '' && ! str_contains($configured, 'localhost')
            ? $configured
            : rtrim(url('/'), '/');
    }
}
