<?php
$sensor1 = $_GET['sensor1'] ?? null;
$sensor2 = $_GET['sensor2'] ?? null;

if ($sensor1 !== null && $sensor2 !== null) {
    $data = date("Y-m-d H:i:s") . " - Sensor1: $sensor1, Sensor2: $sensor2\n";
    file_put_contents("daten.txt", $data, FILE_APPEND);
    echo "OK";
} else {
    echo "Fehlende Daten!";
}
?>