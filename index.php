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
        // Wir zeigen nur den neuesten Eintrag pro Winkel (max 20)
        $maxWinkel = [];
        $anzeigen = [];

        // Neueste zuerst
        $daten = array_reverse($daten);

        foreach ($daten as $eintrag) {
            $winkel = intval($eintrag['sensor1']);
            if (!isset($maxWinkel[$winkel])) {
                $maxWinkel[$winkel] = true;
                $anzeigen[] = $eintrag;
            }
            if (count($anzeigen) >= 20) break;
        }

        foreach (array_reverse($anzeigen) as $e) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($e['datum']) . "</td>";
            echo "<td>" . htmlspecialchars($e['zeit']) . "</td>";
            echo "<td>" . intval($e['sensor1']) . "</td>";
            echo "<td>" . intval($e['sensor2']) . "</td>";
            echo "</tr>";
        }

        // Für JS-Radar-Daten: aktueller Satz (max 20)
        echo "<script>var radardaten = " . json_encode($anzeigen) . ";</script>";
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
    // Zahlen vergleichen falls möglich
    const numA = parseFloat(valA);
    const numB = parseFloat(valB);
    if (!isNaN(numA) && !isNaN(numB)) {
      return asc ? numA - numB : numB - numA;
    }
    return asc ? valA.localeCompare(valB) : valB.localeCompare(valA);
  });
  table.dataset.sort = asc ? "asc" : "desc";
  rows.forEach(row => table.tBodies[0].appendChild(row));
}

function zeichneRadar() {
  const canvas = document.getElementById("radarCanvas");
  const ctx = canvas.getContext("2d");
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  const mitteX = canvas.width / 2;
  const mitteY = canvas.height;
  const maxDist = 300; // Max Distanz, entspricht Radius 200px

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
  if (typeof radardaten !== "undefined") {
    radardaten.forEach(p => {
      const winkel = p.winkel || p.sensor1;
      const dist = p.dist || p.sensor2;
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

setInterval(() => {
  location.reload();
}, 500);
</script>
</body>
</html>
