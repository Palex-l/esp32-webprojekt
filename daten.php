<?php
$dataFile = "daten.json";
$daten = [];

if (file_exists($dataFile)) {
    $json = file_get_contents($dataFile);
    $daten = json_decode($json, true);
}

$tabelle = $daten;
$radar = [];
$winkelSet = [];

for ($i = count($daten) - 1; $i >= 0; $i--) {
    $e = $daten[$i];
    $winkel = $e['sensor1'];
    if (!isset($winkelSet[$winkel])) {
        $radar[] = ['winkel' => $winkel, 'dist' => $e['sensor2']];
        $winkelSet[$winkel] = true;
    }
    if (count($radar) >= 20) break;
}

$radar = array_reverse($radar);

header('Content-Type: application/json');
echo json_encode([
    'tabelle' => $tabelle,
    'radar' => $radar
]);
