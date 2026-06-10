<?php
require_once "../config/database.php";
require_once "../includes/auth.php";
require_once "../includes/lang.php";

$lang = loadLang();
requireAdmin();

$employee_id = $_GET['employee_id'] ?? 0;

$employees = $pdo->query("SELECT * FROM employees ORDER BY name")->fetchAll();

/* selected employee */
$selectedEmployee = null;
if ($employee_id) {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id=?");
    $stmt->execute([$employee_id]);
    $selectedEmployee = $stmt->fetch();
}

/* Fetch paired COME/GO sessions */
function calculateHours($rows) {
    $total = 0;
    $lastCome = null;

    foreach ($rows as $r) {
        if ($r['action'] === 'come') {
            $lastCome = strtotime($r['entry_time']);
        }

        if ($r['action'] === 'go' && $lastCome) {
            $total += (strtotime($r['entry_time']) - $lastCome);
            $lastCome = null;
        }
    }

    return $total / 3600;
}

$data = [];
if ($employee_id) {
    $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id=? ORDER BY entry_time");
    $stmt->execute([$employee_id]);
    $data = $stmt->fetchAll();
}

$totalHours = calculateHours($data);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= $lang["report"] ?? "Report" ?></title>
<link rel="stylesheet" href="/assets/css/style.css">

<style>
.print-box {
    max-width: 900px;
    margin: auto;
    background: white;
    padding: 20px;
}

@media print {
    .no-print { display:none; }
}
</style>
</head>

<body>

<div class="header no-print">
    <?= $lang["work_report"] ?? "Work Report" ?>
</div>

<div class="print-box">

<form class="no-print" method="GET">
    <select name="employee_id">
        <option value="">
            <?= $lang["select_employee"] ?? "Select Employee" ?>
        </option>

        <?php foreach($employees as $e): ?>
            <option value="<?= $e['id'] ?>" <?= $employee_id==$e['id']?'selected':'' ?>>
                <?= htmlspecialchars($e['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button class="btn-come">
        <?= $lang["load"] ?? "Load" ?>
    </button>
</form>

<?php if($employee_id && $selectedEmployee): ?>

<h2>
    <?= $lang["employees"] ?? "Employee" ?>:
    <?= htmlspecialchars($selectedEmployee['name']) ?>
</h2>

<h3>
    <?= $lang["total_hours"] ?? "Total Hours" ?>: <?= round($totalHours,2) ?>
</h3>

<table border="1" width="100%">
<tr>
    <th><?= $lang["date"] ?? "Date" ?></th>
    <th><?= $lang["action"] ?? "Action" ?></th>
    <th><?= $lang["time"] ?? "Time" ?></th>
</tr>

<?php foreach($data as $r): ?>
<tr>
    <td><?= date("Y-m-d", strtotime($r['entry_time'])) ?></td>
    <td>
        <?php
        if ($r['action'] === 'come') {
            echo $lang['come'] ?? 'COME';
        } elseif ($r['action'] === 'go') {
            echo $lang['go'] ?? 'GO';
        } else {
            echo $lang['no_booking'] ?? strtoupper($r['action']);
        }
        ?>
    </td>
    <td><?= date("H:i", strtotime($r['entry_time'])) ?></td>
</tr>
<?php endforeach; ?>
</table>

<br>

<button onclick="window.print()" class="btn-go no-print">
    <?= $lang["print_report"] ?? "Print Report" ?>
</button>

<?php endif; ?>

</div>

</body>
</html>
