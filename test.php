<?php
try {
    $pdo = new PDO('mysql:host=localhost;port=3306;dbname=autogest_garage', 'root', '');
    echo "Connexion OK !";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}