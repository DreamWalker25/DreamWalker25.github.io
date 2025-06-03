<?php
// Klucz API
if (!file_get_contents('api_key.txt')== false) {
    $apiKey = file_get_contents('api_key.txt');
}



// Jeśli formularz został wysłany
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['city'])) {
    $city = urlencode(trim($_POST['city']));

    // 1. Pobierz współrzędne geograficzne miasta
    $geoUrl = "http://api.openweathermap.org/geo/1.0/direct?q={$city}&limit=1&appid={$apiKey}";
    $geoResponse = file_get_contents($geoUrl);
    $geoData = json_decode($geoResponse, true);

    if (!empty($geoData)) {
        $lat = $geoData[0]['lat'];
        $lon = $geoData[0]['lon'];

        // 2. Pobierz dane pogodowe na podstawie współrzędnych
        $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&units=metric&lang=pl&appid={$apiKey}";
        $weatherResponse = file_get_contents($weatherUrl);
        $weatherData = json_decode($weatherResponse, true);
    } else {
        $error = "Nie znaleziono miasta.";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Sprawdź pogodę</title>
</head>
<body>
    <h1>Sprawdź pogodę w swoim mieście</h1>
    <form method="POST">
        <label for="city">Miasto:</label>
        <input type="text" name="city" id="city" required>
        <button type="submit">Szukaj</button>
    </form>

    <?php if (isset($weatherData)): ?>
        <h2>Pogoda w <?= htmlspecialchars($_POST['city']) ?></h2>
        <p>Temperatura: <?= $weatherData['main']['temp'] ?> °C</p>
        <p>Opis: <?= $weatherData['weather'][0]['description'] ?></p>
        <p>Wilgotność: <?= $weatherData['main']['humidity'] ?>%</p>
        <p>Wiatr: <?= $weatherData['wind']['speed'] ?> m/s</p>
    <?php elseif (isset($error)): ?>
        <p style="color:red;"><?= $error ?></p>
    <?php endif; ?>
</body>
</html>
