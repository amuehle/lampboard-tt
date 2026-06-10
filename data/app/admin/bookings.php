<?php
require_once "../config/database.php";
require_once "../includes/auth.php";
require_once "../includes/lang.php";

$lang = loadLang();
requireAdmin();

$employee_id = $_GET["employee_id"] ?? null;

$sql = "
SELECT
    t.id,
    e.name,
    t.action,
    t.entry_time
FROM time_entries t
JOIN employees e ON e.id = t.employee_id
WHERE 1=1
";

$params = [];

if ($employee_id) {
    $sql .= " AND e.id = ?";
    $params[] = $employee_id;
}

$sql .= " ORDER BY t.entry_time DESC LIMIT 200";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$entries = $stmt->fetchAll();

$employees = $pdo->query("SELECT * FROM employees ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= $lang["time_entries"] ?? "Time Entries" ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>

<div class="header"><?= $lang["time_entries"] ?? "Time Entries" ?></div>

<div style="padding:20px;text-align:center;">

    <!-- FILTER -->
    <form method="GET" style="margin-bottom:20px;">
        <select name="employee_id" style="padding:10px;">
            <option value=""><?= $lang["all_employees"] ?? "All Employees" ?></option>

            <?php foreach ($employees as $e): ?>
                <option value="<?= $e["id"] ?>"
                    <?= ($employee_id == $e["id"]) ? "selected" : "" ?>>
                    <?= htmlspecialchars($e["name"]) ?>
                </option>
            <?php endforeach; ?>

        </select>

        <button class="btn-come"><?= $lang["filter"] ?? "Filter" ?></button>
    </form>

    <!-- TABLE -->
    <table style="width:100%;max-width:800px;margin:auto;background:white;border-radius:10px;">
        <tr>
            <th><?= $lang["employee"] ?? "Employee" ?></th>
            <th><?= $lang["action"] ?? "Action" ?></th>
            <th><?= $lang["time"] ?? "Time" ?></th>
        </tr>

        <?php foreach ($entries as $t): ?>
        <tr>
            <td><?= htmlspecialchars($t["name"]) ?></td>
            <td><?= strtoupper($t["action"]) ?></td>
            <td><?= date("d.m.Y H:i", strtotime($t["entry_time"])) ?></td>
        </tr>
        <?php endforeach; ?>

    </table>

</div>

<script>
const LANG = <?= json_encode($lang, JSON_UNESCAPED_UNICODE) ?>;
</script>

</body>
</html>
