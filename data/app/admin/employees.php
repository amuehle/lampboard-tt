<?php
require_once "../config/database.php";
require_once "../includes/auth.php";
require_once "../includes/lang.php";

$lang = loadLang();
requireAdmin();

/* ADD */
if (isset($_POST["name"])) {

    $stmt = $pdo->prepare("
        INSERT INTO employees (name, excel_rounding_enabled, janitor_exception)
        VALUES (?, 0, 0)
    ");

    $stmt->execute([trim($_POST["name"])]);
    header("Location: employees.php");
    exit;
}

/* UPDATE */
if (isset($_POST["edit_id"])) {

    $id = (int)$_POST["edit_id"];
    $name = trim($_POST["edit_name"] ?? '');

    $rounding = isset($_POST["excel_rounding_enabled"]) ? 1 : 0;
    $janitor  = isset($_POST["janitor_exception"]) ? 1 : 0;

    $stmt = $pdo->prepare("
        UPDATE employees
        SET name = ?,
            excel_rounding_enabled = ?,
            janitor_exception = ?
        WHERE id = ?
    ");

    $stmt->execute([$name, $rounding, $janitor, $id]);

    header("Location: employees.php");
    exit;
}

/* DELETE */
if (isset($_GET["delete"])) {

    $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->execute([(int)$_GET["delete"]]);

    header("Location: employees.php");
    exit;
}

$employees = $pdo->query("SELECT * FROM employees ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= $lang["employees"] ?? "Employees" ?></title>
<link rel="stylesheet" href="/assets/css/style.css">

<style>
.row-box {
    display:flex;
    flex-direction:column;
    gap:6px;
}
.flags {
    display:flex;
    gap:14px;
    align-items:center;
    font-size:14px;
    flex-wrap:wrap;
}
.flags label {
    display:flex;
    gap:6px;
    align-items:center;
}
.small-info {
    font-size:12px;
    color:#666;
}
</style>

</head>

<body>

<div class="header">
    <?= $lang["employee_management"] ?? "Employee Management" ?>
</div>

<div style="padding:20px;text-align:center;">

<!-- ADD -->
<form method="POST">
    <input type="text"
           name="name"
           required
           placeholder="<?= $lang["employee_name"] ?? "Employee name" ?>">

    <button class="btn-come">
        <?= $lang["add"] ?? "Add" ?>
    </button>
</form>

<br>

<table style="width:100%;max-width:900px;margin:auto;background:white;border-radius:10px;">
<tr>
    <th>ID</th>
    <th><?= $lang["name"] ?? "Name" ?></th>
    <th><?= $lang["action"] ?? "Action" ?></th>
</tr>

<?php foreach ($employees as $e): ?>
<tr>

<td><?= $e["id"] ?></td>

<td>
<form method="POST" class="row-box">

    <input type="hidden" name="edit_id" value="<?= $e['id'] ?>">

    <input type="text"
           name="edit_name"
           value="<?= htmlspecialchars($e['name']) ?>">

    <div class="flags">

        <span class="small-info">
            <?= $lang["time_rounding_rules"] ?? "Rules active in Excel export only" ?>
        </span>

        <label>
            <input type="checkbox"
                   name="excel_rounding_enabled"
                   <?= $e['excel_rounding_enabled'] ? 'checked' : '' ?>>
            <?= $lang["rounding_enabled"] ?? "Rounding" ?>
        </label>

        <label>
            <input type="checkbox"
                   name="janitor_exception"
                   <?= $e['janitor_exception'] ? 'checked' : '' ?>>
            <?= $lang["janitor_exception"] ?? "Janitor" ?>
        </label>

    </div>

    <button class="btn-go">
        OK
    </button>

</form>
</td>

<td>
    <a href="?delete=<?= $e["id"] ?>"
       onclick="return confirm('<?= $lang["confirm_delete_employee"] ?? "Delete employee?" ?>')">
        <?= $lang["delete"] ?? "Delete" ?>
    </a>
</td>

</tr>
<?php endforeach; ?>

</table>

</div>

</body>
</html>
