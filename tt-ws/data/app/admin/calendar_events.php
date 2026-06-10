<?php
require_once "../config/database.php";
require_once "../includes/lang.php";

$lang = loadLang();


$employee_id = (int)($_GET['employee_id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT * FROM time_entries
    WHERE employee_id=?
    ORDER BY entry_time
");
$stmt->execute([$employee_id]);

$events = [];

foreach ($stmt as $row) {

    $color = $row['action'] === 'come' ? '#28a745' : '#dc3545';

    $title = $row['action'] === 'come'
        ? ($lang['come'] ?? 'COME')
        : ($lang['go'] ?? 'GO');

    $events[] = [
        "id" => $row["id"],
        "title" => $title,
        "start" => $row['entry_time'],
        "color" => $color,
        "extendedProps" => [
            "action" => $row["action"]
        ]
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($events, JSON_UNESCAPED_UNICODE);
