<?php $city = isset($_POST['city']) ? trim($_POST['city']) : 'Gliwice'; // Domyślne miasto
include 'weather.php';
include 'forecast.php';
include 'config.php';


?>
<!DOCTYPE html>
<html lang="pl">

<head>
  <?php
  if (isset($error)) {
    echo "<div class='alert alert-danger'>$error</div>";
  }
  ?>
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

    <form method="POST" class="d-flex justify-content-center mb-4">
      <input id="miasto" type="text" name="city" class="form-control w-50 me-2" placeholder="Wpisz miasto" required>
      <button id="miastobutton" type="submit" class="btn btn-primary">Sprawdź</button>
    </form>

    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    // Funkcja pomocnicza do pobierania danych cURL-em
    function fetchDataCurl($url)
    {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // jeśli jest redirect
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // na testy — wyłącz w produkcji!
      curl_setopt($ch, CURLOPT_TIMEOUT, 10); // limit 10s

      $response = curl_exec($ch);
      if ($response === false) {
        echo "<div class='alert alert-danger'>cURL error: " . curl_error($ch) . "</div>";
      } else {
        echo "<div class='alert alert-success'>cURL success: " . strlen($response) . " bytes</div>";
      }

      curl_close($ch);
      return $response;
    }


    // Klucz API
    // $apiKey = null;
    // if (file_exists('api_key.txt')) {
    //   $apiKey = trim(file_get_contents('api_key.txt'));
    //   echo "<script>console.log('Klucz API wczytany pomyślnie.');</script>";
    // }
    $apiKey = null;
    if (file_exists('api_key.txt')) {
      $apiKey = trim(file_get_contents('api_key.txt'));
      echo "<div class='alert alert-success'>Klucz API wczytany</div>";
    } else {
      echo "<div class='alert alert-danger'>Brak pliku api_key.txt</div>";
    }

    if ($apiKey && $_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['city'])) {
      $city = urlencode(trim($_POST['city']));
      echo "<div>Miasto: " . htmlspecialchars($city) . "</div>";

      // Pobierz współrzędne geograficzne
      $geoUrl = "http://api.openweathermap.org/geo/1.0/direct?q={$city}&limit=1&appid={$apiKey}";
      echo "<div>Geo URL: " . htmlspecialchars($geoUrl) . "</div>";

      $geoResponse = fetchDataCurl($geoUrl);
      if ($geoResponse === false) {
        echo "<div class='alert alert-danger'>Błąd połączenia z serwerem geolokalizacji.</div>";
      } else {
        echo "<pre>Geo response: " . htmlspecialchars($geoResponse) . "</pre>";

        $geoData = json_decode($geoResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
          echo "<div class='alert alert-danger'>Błąd dekodowania JSON: " . json_last_error_msg() . "</div>";
        } else {
          echo "<pre>";
          print_r($geoData);
          echo "</pre>";

          if (!empty($geoData) && isset($geoData[0]['lat'], $geoData[0]['lon'])) {
            $lat = $geoData[0]['lat'];
            $lon = $geoData[0]['lon'];
            echo "<div>Lat: $lat, Lon: $lon</div>";

            // Pobierz dane pogodowe
            $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&units=metric&lang=pl&appid={$apiKey}";
            echo "<div>Weather URL: " . htmlspecialchars($weatherUrl) . "</div>";

            $weatherResponse = fetchDataCurl($weatherUrl);
            if ($weatherResponse === false) {
              echo "<div class='alert alert-danger'>Nie udało się pobrać danych pogodowych.</div>";
            } else {
              echo "<pre>Weather response: " . htmlspecialchars($weatherResponse) . "</pre>";
              $weatherData = json_decode($weatherResponse, true);
              if (json_last_error() !== JSON_ERROR_NONE) {
                echo "<div class='alert alert-danger'>Błąd dekodowania JSON pogody: " . json_last_error_msg() . "</div>";
              } else {
                echo "<pre>";
                print_r($weatherData);
                echo "</pre>";
              }
            }
          } else {
            echo "<div class='alert alert-warning'>Nie znaleziono współrzędnych dla podanego miasta.</div>";
          }
        }
      }
    }
    // // Jeśli formularz został wysłany
    // if ($apiKey && $_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['city'])) {
    //   echo "<script>console.log('Formularz został wysłany.');</script>";
    //   $city = urlencode(trim($_POST['city']));
    //   // 1. Pobierz współrzędne geograficzne miasta
    //   $geoUrl = "http://api.openweathermap.org/geo/1.0/direct?q={$city}&limit=1&appid={$apiKey}";
    //   $geoResponse = fetchDataCurl($geoUrl);

    //   echo "<script>console.log('Zapytanie geolokalizacyjne: " . htmlspecialchars($geoUrl) . "');</script>";

    //   if ($geoResponse !== false) {
    //     $geoData = json_decode($geoResponse, true);
    //     if (json_last_error() !== JSON_ERROR_NONE) {
    //       $error = "Nieprawidłowy format danych JSON: " . json_last_error_msg();
    //       $geoData = null;
    //       error_log($error); // Log the error for debugging
    //     }
    //     echo "<script>console.log('Dane geolokalizacyjne: " . json_encode($geoData) . "');</script>";

    //     if ($geoData !== null) {
    //       $lat = $geoData[0]['lat'];
    //       $lon = $geoData[0]['lon'];

    //       // 2. Pobierz dane pogodowe
    //       $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&units=metric&lang=pl&appid={$apiKey}";
    //       $weatherResponse = fetchDataCurl($weatherUrl);

    //       if ($weatherResponse !== false) {
    //         $weatherData = json_decode($weatherResponse, true);
    //         echo "<script>console.log(" . json_encode($weatherData) . ");</script>";
    //       } else {
    //         $error = "Nie udało się pobrać danych pogodowych.";
    //       }
    //     } else {
    //       $error = "Nie znaleziono miasta.";
    //     }
    //   } else {
    //     $error = "Błąd połączenia z serwerem geolokalizacji.";
    //   }
    // } elseif (!$apiKey) {
    //   $error = "Brak klucza API.";
    // }
    ?>


    <h2 class="mt-5 text-center">Twoja lokalizacja na mapie</h2>
    <div id="map"></div>

    <div id="region-info" class="bg-light p-4 rounded shadow-sm">
      <?php if (isset($weatherData)): ?>
        <h2>Pogoda w <?= htmlspecialchars($city) ?></h2>
        <h2>Pogoda w <?= htmlspecialchars($_POST['city']) ?></h2>
        <p>Temperatura: <?= $weatherData['main']['temp'] ?> °C</p>
        <p>Opis: <?= $weatherData['weather'][0]['description'] ?></p>
        <p>Wilgotność: <?= $weatherData['main']['humidity'] ?>%</p>
        <p>Wiatr: <?= $weatherData['wind']['speed'] ?> m/s</p>
      <?php elseif (isset($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
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

    fetch('api_key.txt')
      .then(response => response.text())
      .then(text => {
        apiKey = text.trim();

        document.getElementById('miastobutton').addEventListener('click', async function(e) {
          e.preventDefault();

          const cityName = document.getElementById('miasto').value.trim();
          if (!cityName) return;

          const apiUrl = `https://api.openweathermap.org/geo/1.0/direct?q=${encodeURIComponent(cityName)}&limit=1&appid=${apiKey}`;

          try {
            const response = await fetch(apiUrl);
            const data = await response.json();

            // === WALIDACJA DANYCH ===
            if (!data || data.length === 0 || !data[0].lat || !data[0].lon) {
              alert('Nie znaleziono lokalizacji lub błąd klucza API.');
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