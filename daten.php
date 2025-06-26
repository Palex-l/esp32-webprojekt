<?php
$datei = "/tmp/daten.json"; // oder daten.txt falls du das verwendest, dann parse entsprechend

if (file_exists($datei)) {
    header('Content-Type: application/json');
    echo file_get_contents($datei);
} else {
    echo json_encode([]);
}
?>