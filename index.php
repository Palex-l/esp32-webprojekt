<!DOCTYPE html>
<html lang="de">
<head>
  <meta http-equiv="refresh" content="2">
  <meta charset="UTF-8">
  <title>Sensorwerte</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
      margin-top: 50px;
    }
    .wert {
      font-size: 2em;
      margin: 10px;
    }
  </style>
</head>
<body>

<h1>Aktuelle Sensordaten</h1>

<?php
$filename = "/tmp/daten.txt";  // Pfad an Render angepasst
$letzteZeile = "";

if (file_exists($filename)) {
    $zeilen = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($zeilen && count($zeilen) > 0) {
        $letzteZeile = end($zeilen);
    }
}

if ($letzteZeile) {
    // Beispiel: Sensor1: 55, Sensor2: 622
    if (preg_match("/Sensor1: (\d+), Sensor2: (\d+)/", $letzteZeile, $matches)) {
        echo "<div class='wert'>Sensor 1: <strong>{$matches[1]}</strong></div>";
        echo "<div class='wert'>Sensor 2: <strong>{$matches[2]}</strong></div>";
    } else {
        echo "<p>Keine gültigen Sensordaten gefunden.</p>";
    }
} else {
    echo "<p>Noch keine Daten vorhanden.</p>";
}
?>

</body>
</html>
