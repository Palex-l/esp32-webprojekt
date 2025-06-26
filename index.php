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
  <meta charset="UTF-8" />
  <title>Radar Visualisierung</title>
  <style>
    /* deine Styles ... */
  </style>
</head>
<body>
  <h1>Radar Visualisierung</h1>
  <canvas id="radarCanvas" width="500" height="250"></canvas>

  <h2>Letzte Messwerte</h2>
  <table id="sensortabelle">
    <thead>
      <tr>
        <th>Datum</th>
        <th>Zeit</th>
        <th>Winkel</th>
        <th>Distanz</th>
      </tr>
    </thead>
    <tbody>
      <!-- Wird per JS gefüllt -->
    </tbody>
  </table>

  <script>
    const radarCanvas = document.getElementById("radarCanvas");
    const ctx = radarCanvas.getContext("2d");
    const tableBody = document.querySelector("#sensortabelle tbody");
    const maxDist = 300;
    const mitteX = radarCanvas.width / 2;
    const mitteY = radarCanvas.height;

    function zeichneRadar(daten) {
      ctx.clearRect(0, 0, radarCanvas.width, radarCanvas.height);
      ctx.strokeStyle = "#0f0";
      ctx.lineWidth = 1;

      // Halbkreis & Ringe
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

      // Messpunkte zeichnen
      daten.forEach(p => {
        const winkel = p.winkel;
        const dist = p.dist;
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

    function updateTable(daten) {
      tableBody.innerHTML = ""; // leeren
      daten.forEach(p => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${p.datum}</td>
          <td>${p.zeit}</td>
          <td>${p.winkel}</td>
          <td>${p.dist}</td>
        `;
        tableBody.appendChild(tr);
      });
    }

    async function ladeDaten() {
      try {
        const res = await fetch("daten.php");
        if (!res.ok) throw new Error("Netzwerkfehler");
        const daten = await res.json();
        zeichneRadar(daten);
        updateTable(daten);
      } catch (e) {
        console.error("Fehler beim Laden der Daten:", e);
      }
    }

    // Daten initial laden und alle 0.5 Sekunden aktualisieren
    ladeDaten();
    setInterval(ladeDaten, 500);
  </script>
</body>
</html>
