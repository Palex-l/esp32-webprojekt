<?php
$data = $_GET['data'] ?? '';

if (!$data || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2},\d+,\d+$/', $data)) {
    http_response_code(400);
    exit("Ungültige Daten");
}

$datei = "daten.txt";

// Neue Zeile anhängen
file_put_contents($datei, $data . PHP_EOL, FILE_APPEND);

// Datei kürzen, wenn mehr als 1000 Zeilen
$zeilen = file($datei, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (count($zeilen) > 1000) {
    $neueZeilen = array_slice($zeilen, -1000);
    file_put_contents($datei, implode(PHP_EOL, $neueZeilen) . PHP_EOL);
}

echo "OK";
?>

