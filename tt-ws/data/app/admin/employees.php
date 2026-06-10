<?php
require_once "../config/database.php";
require_once "../includes/auth.php";
require_once "../includes/lang.php";

$lang = loadLang();
requireAdmin();

/* ADD */
if (isset($_POST["name"])) {
    $stmt = $pdo->prepare("INSERT INTO employees (name) VALUES (?)");
    $stmt->execute([trim($_POST["name"])]);
    header("Location: employees.php");
    exit;
}

/* UPDATE NAME */
if (isset($_POST["edit_id"], $_POST["edit_name"])) {
    $stmt = $pdo->prepare("UPDATE employees SET name=? WHERE id=?");
    $stmt->execute([trim($_POST["edit_name"]), (int)$_POST["edit_id"]]);
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
</head>

<body>

<div class="header"><?= $lang["employees"] ?? "Employee Management" ?></div>

<div style="padding:20px;text-align:center;">

<form method="POST">
    <input type="text" name="name" required placeholder="<?= $lang["employee_name"] ?? "Employee name" ?>">
    <button class="btn-come"><?= $lang["add"] ?? "Add" ?></button>
</form>

<br>

<table style="width:100%;max-width:700px;margin:auto;background:white;">
<tr>
<th>ID</th>
<th><?= $lang["name"] ?? "Name" ?></th>
<th><?= $lang["action"] ?? "Action" ?></th>
</tr>

<?php foreach ($employees as $e): ?>
<tr>
<td><?= $e["id"] ?></td>

<td>
<form method="POST" style="display:flex;gap:5px;">
    <input type="hidden" name="edit_id" value="<?= $e['id'] ?>">
    <input type="text" name="edit_name" value="<?= htmlspecialchars($e['name']) ?>">
    <button class="btn-go">OK</button>
</form>
</td>

<td>
<a href="?delete=<?= $e["id"] ?>"
   onclick="return confirm('<?= $lang["confirm_delete_employee"] ?? "Delete employee?" ?>')">
    <?= $lang["delete"] ?>
</a>
</td>
</tr>
<?php endforeach; ?>

</table>

</div>

</body>
</html>
