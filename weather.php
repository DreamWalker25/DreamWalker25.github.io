<?php
$weatherData = null;

if (!empty($city) && !empty($apiKey)) {
    $apiUrl = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city)
            . "&units=metric&lang=pl&appid=" . $apiKey;

    $response = @file_get_contents($apiUrl);

    if ($response !== false) {
        $weatherData = json_decode($response, true);
    }
}
?>