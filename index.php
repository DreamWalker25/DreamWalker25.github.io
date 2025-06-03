<?php
$city = $_GET['city'] ?? 'Warsaw';
include 'weather.php';
include 'forecast.php';
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
    <input type="text" name="city" class="form-control w-50 me-2" placeholder="Wpisz miasto" required>
    <button type="submit" class="btn btn-primary">Sprawdź</button>
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
</main>

<?php include 'footer.php'; ?>

<!-- Bootstrap JS (wymagane dla karuzeli) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Mapa: Geolokalizacja -->
<script>
  const defaultLat = 52.237049;
  const defaultLng = 21.017532;
  const map = L.map('map').setView([defaultLat, defaultLng], 6);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap'
  }).addTo(map);

  const marker = L.marker([defaultLat, defaultLng]).addTo(map)
    .bindPopup('Domyślna lokalizacja (Warszawa)').openPopup();

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(position => {
      const lat = position.coords.latitude;
      const lng = position.coords.longitude;

      map.setView([lat, lng], 11);
      marker.setLatLng([lat, lng])
        .setPopupContent('Twoja lokalizacja')
        .openPopup();
    }, () => {
      console.warn("Brak zgody na geolokalizację");
    });
  }
</script>
</body>
</html>
