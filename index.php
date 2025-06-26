<?php
$datei = "/tmp/daten.json";
$daten = [];

if (file_exists($datei)) {
    $jsonInhalt = file_get_contents($datei);
    $daten = json_decode($jsonInhalt, true);
    if (!is_array($daten)) {
        $daten = [];
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Sonar Radar</title>
  <style>
    body {
      font-family: sans-serif;
      text-align: center;
      background-color: #111;
      color: #fff;
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
      background: #222;
      color: #fff;
    }
    th, td {
      padding: 8px;
      border: 1px solid #444;
    }
    th {
      cursor: pointer;
      background-color: #333;
    }
  </style>
</head>
<body>
  <h1>Radar Visualisierung</h1>
  <canvas id="radarCanvas" width="500" height="250"></canvas>

  <h2>Letzte Messwerte</h2>
  <table id="sensortabelle">
    <thead>
      <tr>
        <th onclick="sortTable(0)">Datum</th>
        <th onclick="sortTable(1)">Zeit</th>
        <th onclick="sortTable(2)">Winkel</th>
        <th onclick="sortTable(3)">Distanz</th>
      </tr>
    </thead>
    <tbody id="sensordatenBody">
      <!-- Wird dynamisch geladen -->
    </tbody>
  </table>

  <script>
    let sortDirection = true; // true = asc

    function sortTable(colIndex) {
      const tbody = document.getElementById("sensordatenBody");
      const rows = Array.from(tbody.querySelectorAll("tr"));

      rows.sort((a, b) => {
        const valA = a.cells[colIndex].textContent;
        const valB = b.cells[colIndex].textContent;
        return sortDirection
          ? valA.localeCompare(valB, undefined, { numeric: true })
          : valB.localeCompare(valA, undefined, { numeric: true });
      });

      sortDirection = !sortDirection;
      rows.forEach(row => tbody.appendChild(row));
    }

    function zeichneRadar(radardaten) {
      const canvas = document.getElementById("radarCanvas");
      const ctx = canvas.getContext("2d");
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      const mitteX = canvas.width / 2;
      const mitteY = canvas.height;
      const maxDist = 300;

      ctx.strokeStyle = "#0f0";
      ctx.lineWidth = 1;

      // Ringe
      for (let r = 50; r <= 200; r += 50) {
        ctx.beginPath();
        ctx.arc(mitteX, mitteY, r, Math.PI, 2 * Math.PI);
        ctx.stroke();
      }

      // Linien alle 20°
      for (let winkel = 0; winkel <= 180; winkel += 20) {
        const rad = winkel * Math.PI / 180;
        const x = mitteX + Math.cos(rad) * 200;
        const y = mitteY - Math.sin(rad) * 200;
        ctx.beginPath();
        ctx.moveTo(mitteX, mitteY);
        ctx.lineTo(x, y);
        ctx.stroke();
      }

      // Punkte zeichnen
      radardaten.forEach(p => {
        const winkel = p.sensor1;
        const dist = p.sensor2;
        const radius = (dist / maxDist) * 200;
        const rad = winkel * Math.PI / 180;
        const x = mitteX + Math.cos(rad) * radius;
        const y = mitteY - Math.sin(rad) * radius;

        ctx.beginPath();
        ctx.arc(x, y, 5, 0, 2 * Math.PI);
        ctx.fillStyle = "lime";
        ctx.fill();
        ctx.strokeStyle = "darkgreen";
        ctx.stroke();
      });
    }

    async function ladeDaten() {
      try {
        const res = await fetch('daten.json');
        const daten = await res.json();

        // Letzte Winkel (jeder Winkel nur einmal)
        const letzterWinkel = {};
        const filtered = [];
        for (let i = daten.length - 1; i >= 0; i--) {
          const eintrag = daten[i];
          if (!letzterWinkel[eintrag.sensor1]) {
            letzterWinkel[eintrag.sensor1] = true;
            filtered.push(eintrag);
          }
          if (filtered.length >= 20) break;
        }

        // Tabelle neu bauen
        const tbody = document.getElementById("sensordatenBody");
        tbody.innerHTML = '';
        filtered.forEach(e => {
          const tr = document.createElement("tr");
          tr.innerHTML = `<td>${e.datum}</td><td>${e.zeit}</td><td>${e.sensor1}</td><td>${e.sensor2}</td>`;
          tbody.appendChild(tr);
        });

        zeichneRadar(filtered);
      } catch (err) {
        console.error("Fehler beim Laden:", err);
      }
    }

    ladeDaten();
    setInterval(ladeDaten, 500); // nur Daten nachladen
  </script>
</body>
</html>
