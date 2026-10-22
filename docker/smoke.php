<?php

// Requête de test exécutée au démarrage du conteneur : si la page de connexion
// ne s'affiche pas, on affiche l'erreur exacte (page de diagnostic) au lieu
// d'un « 500 Server Error » muet.

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';

$kernel   = $app->make(Kernel::class);
$response = $kernel->handle($request = Request::create('/login', 'GET'));

if ($response->getStatusCode() >= 500) {
    $e = $response->exception ?? null;
    fwrite(STDERR, $e
        ? get_class($e).' : '.$e->getMessage()."\n".$e->getFile().':'.$e->getLine()
        : 'La page de connexion répond HTTP '.$response->getStatusCode());
    exit(1);
}

$kernel->terminate($request, $response);
