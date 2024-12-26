<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Peta Lokasi</title>
    <link
      rel="stylesheet"
      href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
    />
    <style>
      #map {
        height: 600px;
        width: 100%;
      }
    </style>
  </head>
  <body>
    <?php
    require_once('koneksi.php');

    // Ambil data lokasi dari database
    $sql = "SELECT id, img_dir, latitude, longitude FROM images";
    $result = $conn->query($sql); $locations = array(); if ($result->num_rows >
    0) { while($row = $result->fetch_assoc()) { $locations[] = array( 'id' =>
    $row['id'], 'img_dir' => $row['img_dir'], 'latitude' => $row['latitude'],
    'longitude' => $row['longitude'] ); } } $conn->close(); ?>

    <h1>Peta Lokasi</h1>
    <label for="photoSelect">Pilih Foto:</label>
    <select id="photoSelect">
      <?php foreach ($locations as $location): ?>
      <option value="<?php echo $location['id']; ?>">
        Foto ID:
        <?php echo $location['id']; ?>
      </option>
      <?php endforeach; ?>
    </select>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
      const locations = <?php echo json_encode($locations); ?>;

      // Inisialisasi peta
      const map = L.map('map').setView([0, 0], 2);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(map);

      const photoSelect = document.getElementById('photoSelect');

      photoSelect.addEventListener('change', () => {
          const selectedId = photoSelect.value;
          const selectedLocation = locations.find(location => location.id == selectedId);

          if (selectedLocation) {
              const lat = parseFloat(selectedLocation.latitude);
              const lon = parseFloat(selectedLocation.longitude);
              const imgDir = selectedLocation.img_dir;

              map.setView([lat, lon], 13);
              L.marker([lat, lon]).addTo(map)
                  .bindPopup(`<img src="${imgDir}" alt="Foto" style="width: 100px; height: auto;">`)
                  .openPopup();
          }
      });

      if (locations.length > 0) {
          photoSelect.value = locations[0].id;
          photoSelect.dispatchEvent(new Event('change'));
      }
    </script>
  </body>
</html>
