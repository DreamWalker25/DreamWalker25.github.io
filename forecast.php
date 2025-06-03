<?php
$forecastUrl = "https://api.openweathermap.org/data/2.5/forecast?q=" . urlencode($city) . "&units=metric&lang=pl&appid=" . $apiKey;

$forecastResponse = @file_get_contents($forecastUrl);
$forecastData = $forecastResponse ? json_decode($forecastResponse, true) : null;
?>