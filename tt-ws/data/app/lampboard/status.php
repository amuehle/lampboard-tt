<?php
require_once "../config/database.php";
require_once "../includes/lang.php";

if (isset($_GET['lang'])) {
    $_GET['lang'] = $_GET['lang'];
}

$lang = loadLang();

$employees = $pdo->query("
SELECT e.*,
(
    SELECT action
    FROM time_entries t
    WHERE t.employee_id = e.id
    ORDER BY entry_time DESC
    LIMIT 1
) AS last_action,
(
    SELECT entry_time
    FROM time_entries t
    WHERE t.employee_id = e.id
    ORDER BY entry_time DESC
    LIMIT 1
) AS last_time
FROM employees e
ORDER BY e.name
")->fetchAll();

foreach ($employees as $emp) {

    $status = $emp['last_action'] ?? null;

    if ($status === 'come') {
        $class = 'in';
        $text = $emp['last_time']
            ? date("d.m.Y H:i", strtotime($emp['last_time'])) . " " . ($lang["come"] ?? "COME")
            : ($lang["come"] ?? "COME");

    } elseif ($status === 'go') {
        $class = 'out';
        $text = $emp['last_time']
            ? date("d.m.Y H:i", strtotime($emp['last_time'])) . " " . ($lang["go"] ?? "GO")
            : ($lang["go"] ?? "GO");

    } else {
        $class = 'none';
        $text = $lang["no_booking"] ?? "NO ENTRY";
    }

    echo "
    <div class='tile $class'
         onclick=\"openModal({$emp['id']}, '" . htmlspecialchars($emp['name'], ENT_QUOTES) . "')\">
        <div class='name'>" . htmlspecialchars($emp['name']) . "</div>
        <div class='info'>$text</div>
    </div>";
}
