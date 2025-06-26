<?php
$filename = "/tmp/daten.json";
$maxLines = 1000;

// Neue Werte aus URL
$s1 = isset($_GET['sensor1']) ? (int)$_GET['sensor1'] : null;
$s2 = isset($_GET['sensor2']) ? (int)$_GET['sensor2'] : null;

if ($s1 === null || $s2 === null) {
    http_response_code(400);
    echo "Fehlende Daten.";
    exit;
}

// Bestehende laden
$daten = file_exists($filename) ? json_decode(file_get_contents($filename), true) : [];

// Neuen Eintrag hinzufügen
$daten[] = [
    "datum" => date("Y-m-d"),
    "zeit" => date("H:i:s"),
    "sensor1" => $s1,
    "sensor2" => $s2
];

// Auf max. 1000 Einträge beschränken
if (count($daten) > $maxLines) {
    $daten = array_slice($daten, -$maxLines);
}

// Datei speichern
if (file_put_contents($filename, json_encode($daten, JSON_PRETTY_PRINT))) {
    echo "OK";
} else {
    http_response_code(500);
    echo "Fehler beim Schreiben.";
}
?>
