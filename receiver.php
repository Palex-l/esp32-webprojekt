<?php
$dataFile = "/tmp/daten.txt";
$maxLines = 1000;

if (isset($_GET['data'])) {
    $data = trim($_GET['data']);

    if (!empty($data)) {
        // Bestehende Daten einlesen
        $lines = file_exists($dataFile) ? file($dataFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

        // Neue Zeile anhängen
        $lines[] = $data;

        // Auf max. 1000 Zeilen kürzen
        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, -$maxLines);
        }

        // Zurückschreiben
        if (file_put_contents($dataFile, implode("\n", $lines)) === false) {
            echo "Kann daten.txt nicht schreiben";
        } else {
            echo "OK";
        }
    }
}

// Blinkstatus setzen
if (isset($_GET['blink'])) {
    $status = $_GET['blink'] === "on" ? "on" : "off";
    file_put_contents("/tmp/blinkstatus.txt", $status);
    echo "Blinkstatus gesetzt: $status";
}
?>
