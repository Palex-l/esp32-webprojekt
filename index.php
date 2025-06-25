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
<html>
<head>
    <meta charset="UTF-8">
    <title>Messdaten</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: center; cursor: pointer; }
        th.sort-asc::after { content: " ▲"; }
        th.sort-desc::after { content: " ▼"; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

<h2>Letzte Messwerte</h2>

<?php if (empty($daten)): ?>
    <p>Noch keine Daten vorhanden.</p>
<?php else: ?>
    <table id="messTabelle">
        <thead>
            <tr>
                <th onclick="sortTable(0)">Datum</th>
                <th onclick="sortTable(1)">Uhrzeit</th>
                <th onclick="sortTable(2)">Sensor 1</th>
                <th onclick="sortTable(3)">Sensor 2</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($daten as $eintrag): ?>
                <tr>
                    <td><?= htmlspecialchars($eintrag['datum']) ?></td>
                    <td><?= htmlspecialchars($eintrag['zeit']) ?></td>
                    <td><?= $eintrag['sensor1'] ?></td>
                    <td><?= $eintrag['sensor2'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
let currentSortCol = null;
let sortAsc = true;

function sortTable(colIndex) {
    const table = document.getElementById("messTabelle");
    const tbody = table.tBodies[0];
    const rows = Array.from(tbody.rows);

    if (currentSortCol === colIndex) {
        sortAsc = !sortAsc;
    } else {
        currentSortCol = colIndex;
        sortAsc = true;
    }

    const ths = table.querySelectorAll("th");
    ths.forEach((th, idx) => {
        th.classList.remove("sort-asc", "sort-desc");
        if (idx === colIndex) {
            th.classList.add(sortAsc ? "sort-asc" : "sort-desc");
        }
    });

    rows.sort((a, b) => {
        const valA = a.cells[colIndex].textContent.trim();
        const valB = b.cells[colIndex].textContent.trim();

        const isNumber = !isNaN(valA) && !isNaN(valB);
        let cmp = 0;

        if (isNumber) {
            cmp = parseFloat(valA) - parseFloat(valB);
        } else {
            cmp = valA.localeCompare(valB);
        }

        return sortAsc ? cmp : -cmp;
    });

    rows.forEach(row => tbody.appendChild(row));
}
</script>

</body>
</html>
