<?php
$city = $_GET['city'] ?? 'Gliwice';
include 'weather.php';
include 'forecast.php';
include 'config.php';
?>
<!DOCTYPE html>
<html lang="pl">

<head>
  <meta charset="UTF-8">
  <title>Pogoda dla <?= htmlspecialchars($city) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

  <link rel="stylesheet" href="style/style.css">
</head>

<body>

  <?php include 'carousel.php'; ?>

  <main class="container my-5">
    <h1 class="text-center mb-4">Pogoda dla miasta: <?= htmlspecialchars($city) ?></h1>

    <form method="get" class="d-flex justify-content-center mb-4">
      <input id="miastoinput" type="text" name="city" class="form-control w-50 me-2" placeholder="Wpisz miasto" required>
      <button id="miasto" type="submit" class="btn btn-primary">Sprawdź</button>
    </form>

    <?php if ($weatherData && $weatherData['cod'] == 200): ?>
      <div class="weather text-center mb-5">
        <img src="https://openweathermap.org/img/wn/<?= $weatherData['weather'][0]['icon'] ?>@2x.png" alt="Ikona pogody">
        <p>Temperatura: <?= $weatherData['main']['temp'] ?>°C</p>
        <p>Opis: <?= $weatherData['weather'][0]['description'] ?></p>
        <p>Wilgotność: <?= $weatherData['main']['humidity'] ?>%</p>
        <p>Wiatr: <?= $weatherData['wind']['speed'] ?> m/s</p>
        <p>Ciśnienie: <?= $weatherData['main']['pressure'] ?> hPa</p>
      </div>

      <h2 class="mb-3">Prognoza 5-dniowa</h2>
      <div class="forecast row g-3">
        <?php foreach ($forecastData['list'] as $entry): ?>
          <?php $date = date('d.m H:i', strtotime($entry['dt_txt'])); ?>
          <div class="col-6 col-md-2">
            <div class="card text-center">
              <div class="card-body">
                <p class="card-text"><?= $date ?></p>
                <img src="https://openweathermap.org/img/wn/<?= $entry['weather'][0]['icon'] ?>.png" alt="icon">
                <p class="card-text"><?= $entry['main']['temp'] ?>°C</p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    <?php else: ?>
      <p class="text-danger text-center">Nie udało się pobrać danych pogodowych dla <strong><?= htmlspecialchars($city) ?></strong>.</p>
    <?php endif; ?>

    <h2 class="mt-5 text-center">Twoja lokalizacja na mapie</h2>
    <div id="map" class="my-4 rounded shadow" style="height: 400px;"></div>
    <div id="region-info" class="bg-light p-4 rounded shadow-sm">
  <h3 class="mb-3">Informacje o wybranym obszarze</h3>
  <p id="info-content">Kliknij na mapie lub wybierz miasto, aby zobaczyć szczegóły.</p>
</div>
  </main>

  <?php include 'footer.php'; ?>

  <!-- Bootstrap JS (wymagane dla karuzeli) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <!-- Mapa: Geolokalizacja -->
  <script>
  const defaultLat = 50.29249;
  const defaultLng = 18.67201;

  const map = L.map('map').setView([defaultLat, defaultLng], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap'
  }).addTo(map);

  const marker = L.marker([defaultLat, defaultLng]).addTo(map)
    .bindPopup('Domyślna lokalizacja (Gliwice)');

  let apiKey = '';

  // Wczytanie klucza API i dopiero potem aktywacja funkcji
  fetch('api_key.txt')
    .then(response => response.text())
    .then(text => {
      apiKey = text.trim();

      // Po poprawnym załadowaniu API Key - aktywujemy obsługę formularza
      document.getElementById('miasto').addEventListener('click', async function (e) {
        e.preventDefault();

        const cityName = document.getElementById('miastoinput').value.trim();
        if (!cityName) return;

        const apiUrl = `https://api.openweathermap.org/geo/1.0/direct?q=${encodeURIComponent(cityName)}&limit=1&appid=${apiKey}`;

        try {
          const response = await fetch(apiUrl);
          const data = await response.json();

          // Sprawdzenie poprawności odpowiedzi
          if (!data || data.length === 0 || !data[0].lat || !data[0].lon) {
            alert('Nie znaleziono lokalizacji.');
            return;
          }

          const lat = data[0].lat;
          const lng = data[0].lon;

          map.setView([lat, lng], 13);
          marker.setLatLng([lat, lng])
            .setPopupContent(`Wybrana lokalizacja: ${cityName}`)
            .openPopup();
        } catch (error) {
          console.error('Błąd podczas pobierania danych geolokalizacyjnych:', error);
          alert('Wystąpił błąd podczas pobierania danych.');
        }
      });
    })
    .catch(err => {
      console.error('Błąd wczytywania klucza API:', err);
      alert('Nie udało się wczytać klucza API.');
    });
</script>
</body>

</html>