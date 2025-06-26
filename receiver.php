<?php
$dataFile = "/tmp/daten.json";
$maxLines = 1000;

if (isset($_GET['status'])) {
    $status = file_exists("/tmp/status.txt") ? trim(file_get_contents("/tmp/status.txt")) : "stop";
    echo $status;
    exit;
}

if (isset($_GET['data'])) {
    $data = trim($_GET['data']);

    if (!empty($data)) {
        // Format: 2025-06-25 14:23:01,20,135
        if (preg_match("/^([\d\-]+) ([\d:]+),(\d+),(\d+)$/", $data, $match)) {
            $eintrag = [
                "datum" => $match[1],
                "zeit" => $match[2],
                "sensor1" => (int)$match[3],
                "sensor2" => (int)$match[4],
            ];

            $bestehend = [];

            if (file_exists($dataFile)) {
                $json = file_get_contents($dataFile);
                $bestehend = json_decode($json, true);
                if (!is_array($bestehend)) {
                    $bestehend = [];
                }
            }

            $bestehend[] = $eintrag;

            if (count($bestehend) > $maxLines) {
                $bestehend = array_slice($bestehend, -$maxLines);
            }

            if (file_put_contents($dataFile, json_encode($bestehend, JSON_PRETTY_PRINT)) === false) {
                echo "Fehler beim Schreiben von daten.json";
            } else {
                echo "OK";
            }
        } else {
            echo "Ungültiges Datenformat";
        }
    }
}
?>
