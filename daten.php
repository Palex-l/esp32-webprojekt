<?php
header('Content-Type: application/json');

$datei = "/tmp/daten.json";
$daten = [];

if (file_exists($datei)) {
    $json = file_get_contents($datei);
    $daten = json_decode($json, true);
    if (!is_array($daten)) {
        $daten = [];
    }
}

echo json_encode($daten);
?>
