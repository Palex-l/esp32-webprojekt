<?php
$datei = "/tmp/daten.txt";
$zeilen = file_exists($datei) ? file($datei, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

$daten = [];

foreach ($zeilen as $zeile) {
    if (preg_match("/^([\d\-]+) ([\d:]+),(\d+),(\d+)$/", $zeile, $match)) {
        $daten[] = [
            'datum' => $match[1],
            'zeit' => $match[2],
            'sensor1' => (int)$match[3],
            'sensor2' => (int)$match[4],
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Live Sensordaten</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f4f4f4; }
        table { border-collapse: collapse; width: 100%; background: white; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { cursor: pointer; background-color: #eee; }
        .btn {
            background-color: #4CAF50; color: white;
            padding: 10px 20px; border: none;
            cursor: pointer; margin-top: 20px;
        }
    </style>
</head>
<body>

<h1>Live Sensordaten</h1>

<?php
$dataFile = "/tmp/daten.txt";
if (!file_exists($dataFile)) {
    echo "<p>Noch keine Daten vorhanden.</p>";
} else {
    $lines = file($dataFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "<table id='sensortabelle'>
            <thead><tr>
                <th onclick='sortTable(0)'>Datum</th>
                <th onclick='sortTable(1)'>Uhrzeit</th>
                <th onclick='sortTable(2)'>Sensor 1</th>
                <th onclick='sortTable(3)'>Sensor 2</th>
            </tr></thead><tbody>";
    foreach ($lines as $line) {
        list($datetime, $s1, $s2) = explode(",", $line);
        [$date, $time] = explode(" ", $datetime);
        echo "<tr><td>$date</td><td>$time</td><td>$s1</td><td>$s2</td></tr>";
    }
    echo "</tbody></table>";
}
?>

<button class="btn" onclick="toggleBlink()">🔁 Blink-LED dddumschalten</button>

<script>
let sortAsc = true;

function sortTable(col) {
    const table = document.getElementById("sensortabelle");
    const rows = Array.from(table.rows).slice(1);
    rows.sort((a, b) => {
        const valA = a.cells[col].textContent;
        const valB = b.cells[col].textContent;
        return sortAsc ? valA.localeCompare(valB) : valB.localeCompare(valA);
    });
    sortAsc = !sortAsc;
    rows.forEach(row => table.tBodies[0].appendChild(row));
}

function toggleBlink() {
    fetch("receiver.php?blink=on")
        .then(res => res.text())
        .then(txt => {
            alert("Blinksignal gesendet!");
            console.log("Antwort: ", txt);
        })
        .catch(err => alert("Fehler beim Senden des Blinksignals."));
}
</script>

</body>
</html>
