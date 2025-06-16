<?php $city = isset($_POST['city']) ? $_POST['city'] : 'Gliwice'; ?>
<main class="container my-5">

    <h1 class="text-center mb-4">Pogoda dla miasta: <?= htmlspecialchars($city) ?></h1>

    <form method="POST" class="d-flex justify-content-center mb-4">
        <input id="miasto" type="text" name="city" class="form-control w-50 me-2" placeholder="Wpisz miasto" required>
        <button id="miastobutton" type="submit" class="btn btn-primary">Sprawdź</button>
    </form>

    <?php
    // Funkcja pomocnicza do pobierania danych cURL-em
    function fetchDataCurl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Bezpieczniejsze
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            return false;
        }
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($http_code === 200) ? $response : false;
    }

    // Klucz API
    $apiKey = null;
    if (file_exists('api_key.txt')) {
        $apiKey = trim(file_get_contents('api_key.txt'));
    }

    // Jeśli formularz został wysłany
    if ($apiKey && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['city'])) {
        $city = urlencode(trim($_POST['city']));

        // 1. Pobierz współrzędne geograficzne miasta
        $geoUrl = "https://api.openweathermap.org/geo/1.0/direct?q={$city}&limit=1&appid={$apiKey}";
        $geoResponse = fetchDataCurl($geoUrl);

        if ($geoResponse !== false) {
            $geoData = json_decode($geoResponse, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error = "Nieprawidłowy format danych JSON.";
                $geoData = null;
            }

            if (!empty($geoData)) {
                $lat = $geoData[0]['lat'];
                $lon = $geoData[0]['lon'];

                // 2. Pobierz dane pogodowe
                $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&units=metric&lang=pl&appid={$apiKey}";
                $weatherResponse = fetchDataCurl($weatherUrl);

                if ($weatherResponse !== false) {
                    $weatherData = json_decode($weatherResponse, true);
                    echo "<script>console.log(" . json_encode($weatherData) . ");</script>";
                } else {
                    $error = "Nie udało się pobrać danych pogodowych.";
                }
            } else {
                $error = "Nie znaleziono miasta.";
            }
        } else {
            $error = "Błąd połączenia z serwerem geolokalizacji.";
        }
    } elseif (!$apiKey) {
        $error = "Brak klucza API.";
    }
    ?>
    <?php if (isset($weatherData)): ?>
        <h2>Pogoda w <?= htmlspecialchars($_POST['city']) ?></h2>
        <p>Temperatura: <?= $weatherData['main']['temp'] ?> °C</p>
        <p>Opis: <?= $weatherData['weather'][0]['description'] ?></p>
        <p>Wilgotność: <?= $weatherData['main']['humidity'] ?>%</p>
        <p>Wiatr: <?= $weatherData['wind']['speed'] ?> m/s</p>
    <?php elseif (isset($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>