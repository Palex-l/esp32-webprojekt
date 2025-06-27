<?php

// Pfad zur JSON-Datei
$dataFile = "/tmp/daten.json";

// Maximale Anzahl an Messwerten die in der Datei gespeichert bleiben sollen
$maxLines = 1000;

// FALL 1: Wenn der ESP den aktuellen Status (start/stop) abfragt

// Wenn die URL einen parameter 'status' enthält, z. B. /receiver.php?status=1
if (isset($_GET['status'])) {

    // Prüfe ob Statusdatei existiert. Wenn ja, Inhalt lesen (entweder "start" oder "stop")
    $status = file_exists("/tmp/status.txt") ? trim(file_get_contents("/tmp/status.txt")) : "stop";

    // Sende Status direkt zurück an den ESp
    echo $status;

    // Beende das Skript nach Statusabfrage
    exit;
}

// FALL 2: Wenn Daten übermittelt wurden (mit ?data=...)

// Prüfen ob der Parameter data vorhanden ist, z. B. /receiver.php?data=2025-06-25 14:23:01,30,120
if (isset($_GET['data'])) {

    // entfernt Leerzeichen oder Zeilenumbrüche am Anfang/Ende
    $data = trim($_GET['data']);

    // Nur verarbeiten, wenn die Daten nicht leer sind
    if (!empty($data)) {

        // Formatprüfung auf Datum Uhrzeit,Winkel,Distanz
        // Beispiel: 2025-06-25 14:23:01,30,120
        // Erläuterung der Regex:
        // ([\d\-]+): ein Datumsteil wie 2025-06-25
        // ([\d:]+): eine Uhrzeit wie 14:23:01
        // (\d+),(\d+): zwei positive Ganzzahlen (z. B. Winkel und Distanz)
        if (preg_match("/^([\d\-]+) ([\d:]+),(\d+),(\d+)$/", $data, $match)) {

            // Eintrag wird aus der Zeile gebildet
            $eintrag = [
                "datum" => $match[1],          // zb. "2025-06-25"
                "zeit" => $match[2],           // zb. "14:23:01"
                "sensor1" => (int)$match[3],   // zb. Winkel (0–180°)
                "sensor2" => (int)$match[4],   // zb. Distanz (in mm)
            ];

            // Leeres Array für bereits vorhandene Daten
            $bestehend = [];

            // Wenn die Datei bereits existiert einlesen und dekodieren
            if (file_exists($dataFile)) {
                $json = file_get_contents($dataFile);
                $bestehend = json_decode($json, true);

                // Falls Dateiinhalt ungültig war oder kein Array. leeren
                if (!is_array($bestehend)) {
                    $bestehend = [];
                }
            }

            // Neuen Eintrag hinten an das Array anhängen
            $bestehend[] = $eintrag;

            // Datenmenge begrenzen nur die letzten $maxLines behalten
            if (count($bestehend) > $maxLines) {
                $bestehend = array_slice($bestehend, -$maxLines);
            }

            // Speichern in daten.json im JSON-Format
            $result = file_put_contents($dataFile, json_encode($bestehend, JSON_PRETTY_PRINT));

            // Fehlerausgabe, falls Speichern fehlschlägt
            if ($result === false) {
                echo "Fehler beim Schreiben von daten.json";
            } else {
                echo "OK"; 
            }

        } else {
            // Falls das Format nicht korrekt ist
            echo "Ungültiges Datenformat";
        }
    }
}
?>
