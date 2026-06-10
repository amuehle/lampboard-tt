<?php
require_once "../config/database.php";
require_once "../includes/lang.php";

$lang = loadLang();

$deployment_id = $_GET['deployment_id'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM deployments WHERE deployment_id=?");
$stmt->execute([$deployment_id]);
$deployment = $stmt->fetch();
if (!$deployment) die($lang["invalid_deployment_id"] ?? "Invalid deployment ID");

$today = date('Y-m-d');

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
    ) AS last_time,
    (
        SELECT id
        FROM time_entries t
        WHERE t.employee_id = e.id
        ORDER BY entry_time DESC
        LIMIT 1
    ) AS last_entry_id
    FROM employees e
    WHERE e.active = 1
    ORDER BY e.name
")->fetchAll();

foreach ($employees as &$emp) {
    if (!isset($emp['last_time']) || date('Y-m-d', strtotime($emp['last_time'])) !== $today) {
        $emp['last_action'] = null;
        $emp['last_entry_id'] = null;
    }
}
unset($emp);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= $lang["time_tracker"] ?? "Time Tracker" ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="header">
    <?= $lang["lampentableau_terminal"] ?? "Lampentableau Terminal" ?>
    <div class="clock" id="clock"></div>
</div>

<div class="lampboard" id="lampboard">
<?php foreach ($employees as $emp): ?>
<?php
$status = $emp['last_action'] ?? null;

if ($status === 'come') {
    $class = 'in';
    $text = $emp['last_time']
        ? date("d.m.Y H:i", strtotime($emp['last_time'])) . " " . ($lang["come"] ?? "KOMMEN")
        : ($lang["come"] ?? "COME");
} elseif ($status === 'go') {
    $class = 'out';
    $text = $emp['last_time']
        ? date("d.m.Y H:i", strtotime($emp['last_time'])) . " " . ($lang["go"] ?? "GEHEN")
        : ($lang["go"] ?? "GO");
} else {
    $class = 'none';
    $text = $lang["no_booking"] ?? "KEINE BUCHUNG";
}
?>
<div class="tile <?= $class ?>"
     onclick="openModal(<?= $emp['id'] ?>, '<?= htmlspecialchars($emp['name']) ?>')"
     data-entry="<?= $emp['last_entry_id'] ?>">
    <div class="name"><?= htmlspecialchars($emp['name']) ?></div>
    <div class="info"><?= $text ?></div>
</div>
<?php endforeach; ?>
</div>

<!-- MODAL -->
<div class="modal" id="modal">
    <div class="modal-content">
        <h2 id="modalName" style="font-size:28px;"></h2>
        <button class="btn-come" onclick="book('come')"> <?= $lang["come"] ?? "KOMMEN" ?></button>
        <button class="btn-go" onclick="book('go')"> <?= $lang["go"] ?? "GEHEN" ?></button>
        <button class="btn-cancel" onclick="closeModal()"> <?= $lang["cancel"] ?? "ABBRECHEN" ?></button>
    </div>
</div>

<script>
const LANG = <?= json_encode($lang, JSON_UNESCAPED_UNICODE) ?>;
</script>

<script>
const LANG_CODE = "<?= htmlspecialchars($_GET['lang'] ?? '') ?>";
</script>
<script src="/assets/js/app.js"></script>
</body>
</html>
