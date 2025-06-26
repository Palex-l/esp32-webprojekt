<?php
$datei = "/tmp/daten.txt";
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
      border-radius: 100% 100% 50 50;
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
    <tbody>
      <?php
        $datei = "/tmp/daten.txt";
        $maxWinkel = [];

        if (file_exists($datei)) {
          $zeilen = file($datei, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
          $zeilen = array_reverse($zeilen); // neueste zuerst

          $radardaten = [];

          foreach ($zeilen as $zeile) {
            if (preg_match("/(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2}),(\d+),(\d+)/", $zeile, $matches)) {
              $datum = $matches[1];
              $zeit = $matches[2];
              $winkel = intval($matches[3]);
              $dist = intval($matches[4]);

              if (!isset($maxWinkel[$winkel])) {
                $maxWinkel[$winkel] = true;
                echo "<tr><td>$datum</td><td>$zeit</td><td>$winkel</td><td>$dist</td></tr>";
                $radardaten[] = ['winkel' => $winkel, 'dist' => $dist];
              }

              if (count($radardaten) >= 20) break;
            }
          }

          // Radar-Daten als JSON für JS
          echo "<script>var radardaten = " . json_encode($radardaten) . ";</script>";
        }
      ?>
    </tbody>
  </table>

  <script>
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

    function zeichneRadar() {
      const canvas = document.getElementById("radarCanvas");
      const ctx = canvas.getContext("2d");
      ctx.clearRect(0, 0, 1000, 500);

      const mitteX = 250;
      const mitteY = 250;
      const maxDist = 300; // entspricht Radius 200px

      // Radar Halbkreis & Ringe
      ctx.strokeStyle = "#0f0";
      for (let r = 50; r <= 200; r += 50) {
        ctx.beginPath();
        ctx.arc(mitteX, mitteY, r, Math.PI, 2 * Math.PI);
        ctx.stroke();
      }

      // Linien alle 30°
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
      if (typeof radardaten !== "undefined") {
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
        });
      }
    }

    zeichneRadar();
      
    setTimeout(() => {
     location.reload();
    }, 500); // 5000 Millisekunden = 5 Sekunden
  </script>
</body>
</html>
