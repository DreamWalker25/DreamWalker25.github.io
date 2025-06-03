<?php
$apiKey = 'TWOJ_API_KEY'; // Wstaw tu swój klucz API
$apiUrl = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&units=metric&lang=pl&appid=" . $apiKey;

$response = @file_get_contents($apiUrl);
$weatherData = $response ? json_decode($response, true) : null;
?>