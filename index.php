<?php
$datei = "/tmp/daten.json";
$zeilen = file_exists($datei) ? file($datei, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

$daten = [];

foreach ($zeilen as $zeile) {
    if (preg_match("/^([\d\-]+) ([\d:]+),(\d+),(\d+)$/", $zeile, $match)) {
        $daten[] = [
            'datum' => $match[1],
            'zeit' => $match[2],
            'sensor1' => (int)$match[3],
            'sensor2' => (int)$match[4],
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>ESP32 Sonar Visualisierung</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #121212;
      color: #fff;
      text-align: center;
      margin: 0;
      padding: 0;
    }
    h1 {
      margin-top: 20px;
    }
    #radarCanvas {
      background-color: #000;
      margin: 20px auto;
      display: block;
      border-radius: 100% 100% 0 0;
    }
    table {
      margin: 20px auto;
      border-collapse: collapse;
      width: 80%;
      background-color: #222;
    }
    th, td {
      padding: 8px 12px;
      border: 1px solid #444;
    }
    th {
      background-color: #333;
    }
  </style>
</head>
<body>
  <h1>ESP32 Sonar Visualisierung</h1>
  <canvas id="radarCanvas" width="500" height="250"></canvas>
  <div id="tabelle"></div>

  <script>
    function ladeDaten() {
      fetch('daten.json')
        .then(response => response.json())
        .then(data => {
          zeichneRadar(data);
          baueTabelle(data);
        });
    }

    function zeichneRadar(data) {
      const canvas = document.getElementById('radarCanvas');
      const ctx = canvas.getContext('2d');
      const centerX = canvas.width / 2;
      const centerY = canvas.height;
      const maxDist = 300;
      const radiusScale = canvas.height / maxDist;

      ctx.clearRect(0, 0, canvas.width, canvas.height);

      // Ringe
      ctx.strokeStyle = '#0f0';
      for (let r = 50; r <= maxDist; r += 50) {
        ctx.beginPath();
        ctx.arc(centerX, centerY, r * radiusScale, Math.PI, 0);
        ctx.stroke();
      }

      // Linien
      ctx.strokeStyle = '#060';
      for (let angle = 0; angle <= 180; angle += 30) {
        const rad = angle * Math.PI / 180;
        const x = centerX + Math.cos(rad) * maxDist * radiusScale;
        const y = centerY - Math.sin(rad) * maxDist * radiusScale;
        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.lineTo(x, y);
        ctx.stroke();
      }

      // Daten als Kreisbögen
      data.forEach(entry => {
        const angle = parseInt(entry.sensor1); // Winkel
        const dist = parseInt(entry.sensor2);  // Distanz
        const rad = angle * Math.PI / 180;
        const r = dist * radiusScale;

        ctx.beginPath();
        ctx.arc(centerX, centerY, r, Math.PI - rad - 0.02, Math.PI - rad + 0.02); // schmaler Bogen
        ctx.strokeStyle = 'lime';
        ctx.lineWidth = 3;
        ctx.stroke();
      });
    }

    function baueTabelle(data) {
      let html = "<table><tr><th>Datum</th><th>Zeit</th><th>Winkel</th><th>Distanz</th></tr>";
      data.slice().reverse().forEach(entry => {
        html += `<tr><td>${entry.datum}</td><td>${entry.zeit}</td><td>${entry.sensor1}°</td><td>${entry.sensor2} cm</td></tr>`;
      });
      html += "</table>";
      document.getElementById("tabelle").innerHTML = html;
    }

    // Initial und alle 500ms neu laden
    ladeDaten();
    setInterval(ladeDaten, 500);
  </script>
</body>
</html>
