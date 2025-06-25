<?php
$data = $_GET['data'] ?? '';

if (!$data || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2},\d+,\d+$/', $data)) {
    http_response_code(400);
    exit("Ungültige Daten");
}

$datei = "/tmp/daten.txt";

// Sicherstellen, dass Datei existiert
if (!file_exists($datei)) {
    $handle = fopen($datei, "w");
    if ($handle === false) {
        http_response_code(500);
        exit("Kann daten.txt nicht erstellen");
    }
    fclose($handle);
}

// Neue Zeile anhängen
if (file_put_contents($datei, $data . PHP_EOL, FILE_APPEND) === false) {
    http_response_code(500);
    exit("Kann daten.txt nicht schreiben");
}

// Datei kürzen, wenn mehr als 1000 Zeilen
$zeilen = file($datei, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (count($zeilen) > 1000) {
    $neueZeilen = array_slice($zeilen, -1000);
    file_put_contents($datei, implode(PHP_EOL, $neueZeilen) . PHP_EOL);
}

echo "OK";
?>
