<?php
$sensor1 = $_GET['sensor1'] ?? '';
$sensor2 = $_GET['sensor2'] ?? '';

$filename = "/tmp/daten.txt";  // Pfad an Render angepasst

if ($sensor1 !== '' && $sensor2 !== '') {
    // Daten im Format "Sensor1: 55, Sensor2: 622" mit Zeitstempel speichern
    $zeit = date("Y-m-d H:i:s");
    $data = "$zeit - Sensor1: $sensor1, Sensor2: $sensor2\n";
    file_put_contents($filename, $data, FILE_APPEND | LOCK_EX);
    echo "Daten gespeichert!";
} else {
    echo "Keine Daten empfangen.";
}
?>
