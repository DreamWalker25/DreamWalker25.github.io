<?php
// Wczytaj klucz API z pliku
$apiKeyPath = __DIR__ . '/api_key.txt';
$apiKey = file_exists($apiKeyPath) ? trim(file_get_contents($apiKeyPath)) : null;

// Jeśli klucz nie istnieje – ustaw null i zgłoś błąd
if (!$apiKey) {
    die("Błąd: Nie znaleziono pliku api_key.txt lub plik jest pusty.");
}
?>