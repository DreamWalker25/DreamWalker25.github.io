<?php
$city = isset($_POST['city']) ? trim($_POST['city']) : 'Gliwice';
include 'weather.php';
include 'forecast.php';
include 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

function fetchDataCurl($url)
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  $response = curl_exec($ch);
  curl_close($ch);
  return $response;
}

$apiKey = null;
if (file_exists('api_key.txt')) {
  $apiKey = trim(file_get_contents('api_key.txt'));
}

$weatherData = null;
$lat = 50.29249;
$lon = 18.67201; // domyślnie Gliwice

if ($apiKey && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['city'])) {
  $city = trim($_POST['city']);
  $geoUrl = "http://api.openweathermap.org/geo/1.0/direct?q=" . urlencode($city) . "&limit=1&appid={$apiKey}";
  $geoResponse = fetchDataCurl($geoUrl);

  $geoData = json_decode($geoResponse, true);
  if (!empty($geoData) && isset($geoData[0]['lat'], $geoData[0]['lon'])) {
    $lat = $geoData[0]['lat'];
    $lon = $geoData[0]['lon'];

    $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&units=metric&lang=pl&appid={$apiKey}";
    $weatherResponse = fetchDataCurl($weatherUrl);
    $weatherData = json_decode($weatherResponse, true);
  }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
  <meta charset="UTF-8">
  <title>Pogoda dla <?= htmlspecialchars($city) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="style/style.css">
</head>

<body>
  <?php include 'carousel.php'; ?>

  <main class="container my-5">
  <div class="weather-header">
    <h1>Pogoda dla miasta: <?= htmlspecialchars($city) ?></h1>

    <form method="POST" class="weather-form">
      <input id="miasto" type="text" name="city" class="form-control" placeholder="Wpisz miasto" required>
      <button id="miastobutton" type="submit" class="btn btn-primary">Sprawdź</button>
    </form>
  </div>

  <div class="weather-layout">
    <div class="weather-map">
      <h2>Twoja lokalizacja na mapie</h2>
      <div id="map"></div>
    </div>

    <div id="region-info" class="weather-info">
      <?php if ($weatherData): ?>
        <h2>Pogoda w <?= htmlspecialchars($city) ?></h2>
        <p>Temperatura: <?= $weatherData['main']['temp'] ?> °C</p>
        <p>Opis: <?= $weatherData['weather'][0]['description'] ?></p>
        <p>Wilgotność: <?= $weatherData['main']['humidity'] ?>%</p>
        <p>Wiatr: <?= $weatherData['wind']['speed'] ?> m/s</p>
      <?php else: ?>
        <p class="text-muted">Brak danych pogodowych.</p>
      <?php endif; ?>
    </div>
  </div>
</main>

  <?php include 'footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <script>
    const latFromPHP = <?= json_encode($lat) ?>;
    const lonFromPHP = <?= json_encode($lon) ?>;

    const map = L.map('map').setView([latFromPHP, lonFromPHP], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const marker = L.marker([latFromPHP, lonFromPHP]).addTo(map)
      .bindPopup("Wybrana lokalizacja: <?= htmlspecialchars($city) ?>")
      .openPopup();
  </script>
</body>

</html>