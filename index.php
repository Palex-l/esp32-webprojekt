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
  <div id="tabelle">
    <table id="sensortabelle">
      <thead>
        <tr>
          <th onclick="sortTable(0)">Datum</th>
          <th onclick="sortTable(1)">Zeit</th>
          <th onclick="sortTable(2)">Winkel</th>
          <th onclick="sortTable(3)">Distanz</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (array_reverse($daten) as $eintrag): ?>
          <tr>
            <td><?= $eintrag['datum'] ?></td>
            <td><?= $eintrag['zeit'] ?></td>
            <td><?= $eintrag['sensor1'] ?></td>
            <td><?= $eintrag['sensor2'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <script>
    let radardaten = <?= json_encode($radardaten); ?>;

    function zeichneRadar() {
      const canvas = document.getElementById("radarCanvas");
      const ctx = canvas.getContext("2d");
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      const mitteX = canvas.width / 2;
      const mitteY = canvas.height;
      const maxDist = 300;

      ctx.strokeStyle = "#0f0";
      ctx.lineWidth = 1;

      for (let r = 50; r <= 200; r += 50) {
        ctx.beginPath();
        ctx.arc(mitteX, mitteY, r, Math.PI, 2 * Math.PI);
        ctx.stroke();
      }

      for (let winkel = 0; winkel <= 180; winkel += 20) {
        const rad = winkel * Math.PI / 180;
        const x = mitteX + Math.cos(rad) * 200;
        const y = mitteY - Math.sin(rad) * 200;
        ctx.beginPath();
        ctx.moveTo(mitteX, mitteY);
        ctx.lineTo(x, y);
        ctx.stroke();
      }

      if (radardaten) {
        radardaten.forEach(p => {
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
    }

    zeichneRadar();

    function sortTable(col) {
      const table = document.getElementById("sensortabelle");
      const rows = Array.from(table.rows).slice(1);
      const asc = !table.dataset.sort || table.dataset.sort !== "asc";
      rows.sort((a, b) => {
        const valA = a.cells[col].textContent;
        const valB = b.cells[col].textContent;
        return asc ? valA.localeCompare(valB) : valB.localeCompare(valA);
      });
      table.dataset.sort = asc ? "asc" : "desc";
      rows.forEach(row => table.tBodies[0].appendChild(row));
    }

    // Alle 500ms nur Radar + Tabelle aktualisieren
    setInterval(() => {
      fetch('data.php')
        .then(res => res.json())
        .then(data => {
          radardaten = data.radar;

          // Tabelle neu bauen
          const tbody = document.querySelector("#sensortabelle tbody");
          tbody.innerHTML = "";
          data.tabelle.forEach(eintrag => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
              <td>${eintrag.datum}</td>
              <td>${eintrag.zeit}</td>
              <td>${eintrag.sensor1}</td>
              <td>${eintrag.sensor2}</td>`;
            tbody.appendChild(tr);
          });

          zeichneRadar();
        });
    }, 500);
  </script>
</body>
</html>
</html>
