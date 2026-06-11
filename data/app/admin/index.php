<?php
require_once "../config/database.php";
require_once "../includes/auth.php";
require_once "../includes/lang.php";

$lang = loadLang();
requireAdmin();

/* CHANGE PASSWORD */
$passMsg = "";
if (isset($_POST['new_password'])) {
    $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE admins SET password=? WHERE id=?");
    $stmt->execute([$hash, $_SESSION['admin_id']]);
    $passMsg = $lang["password_updated"] ?? "Password updated successfully";
}

/* DEPLOYMENT ID */
$depMsg = "";
if (isset($_POST['deployment_id'])) {
    $id = trim($_POST['deployment_id']);
    $exists = $pdo->query("SELECT COUNT(*) FROM deployments")->fetchColumn();

    if ($exists == 0) {
        $stmt = $pdo->prepare("INSERT INTO deployments (deployment_id) VALUES (?)");
        $stmt->execute([$id]);
    } else {
        $stmt = $pdo->prepare("UPDATE deployments SET deployment_id=? LIMIT 1");
        $stmt->execute([$id]);
    }
    $depMsg = $lang["deployment_updated"] ?? "Deployment ID updated successfully";
}

$currentDeployment = $pdo->query("SELECT deployment_id FROM deployments LIMIT 1")->fetchColumn();

/* EMPLOYEES FOR EXCEL EXPORT */
$employees = $pdo->query("SELECT * FROM employees ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= $lang["admin_panel"] ?? "Admin Panel" ?></title>
<link rel="stylesheet" href="/assets/css/style.css">

<style>
.tab-container { display:flex; flex-wrap:wrap; margin-top:20px; }
.tab { flex:1; padding:12px; background:#eee; cursor:pointer; text-align:center; font-size:16px; }
.tab.active { background:#ddd; font-weight:bold; }
.tab-content { display:none; margin-top:20px; padding:10px; }
.tab-content.active { display:block; }

.export-box {
    background:#fff;
    padding:15px;
    margin-bottom:10px;
    border-radius:8px;
    box-shadow:0 2px 6px rgba(0,0,0,0.1);
}
</style>
</head>

<body>

<div class="header"><?= $lang["admin_panel"] ?? "Admin Panel" ?></div>

<!-- TABS -->
<div class="tab-container">
    <div class="tab active" data-tab="password"><?= $lang["change_password"] ?? "Change Password" ?></div>
    <div class="tab" data-tab="deployment"><?= $lang["deployment_id"] ?? "Deployment ID" ?></div>
    <div class="tab" data-tab="employees"><?= $lang["employees"] ?? "Employees" ?></div>
    <div class="tab" data-tab="calendar"><?= $lang["calendar"] ?? "Calendar" ?></div>
    <div class="tab" data-tab="excel"><?= $lang["excel_export"] ?? "Excel Export" ?></div>
    <div class="tab" data-tab="reports"><?= $lang["reports"] ?? "Reports" ?></div>
    <div class="tab" data-tab="logout"><?= $lang["logout"] ?? "Logout" ?></div>
</div>

<!-- PASSWORD -->
<div class="tab-content active" id="password">
    <h3><?= $lang["change_password"] ?? "Change Password" ?></h3>

    <form method="POST">
        <input type="password" name="new_password"
               placeholder="<?= $lang["new_password"] ?? "New password" ?>"
               required style="width:100%;padding:12px;">
        <button class="btn-come" style="width:100%;margin-top:10px;">
            <?= $lang["update"] ?? "Update Password" ?>
        </button>
    </form>

    <p><?= $passMsg ?></p>
</div>

<!-- DEPLOYMENT -->
<div class="tab-content" id="deployment">
    <h3><?= $lang["dashboard_access_key"] ?? "Dashboard Access Key" ?></h3>

    <form method="POST">
        <input type="text" name="deployment_id"
               value="<?= htmlspecialchars($currentDeployment) ?>"
               required style="width:100%;padding:12px;">
        <button class="btn-go" style="width:100%;margin-top:10px;">
            <?= $lang["update"] ?? "Update Deployment ID" ?>
        </button>
    </form>

    <p><?= $depMsg ?></p>
</div>

<!-- EMPLOYEES -->
<div class="tab-content" id="employees">
    <h3><?= $lang["manage_employees"] ?? "Manage Employees" ?></h3>

    <a href="/admin/employees.php">
        <button class="btn-come" style="width:100%;">
            <?= $lang["open_employee_manager"] ?? "Open Employee Manager" ?>
        </button>
    </a>
</div>

<!-- CALENDAR -->
<div class="tab-content" id="calendar">
    <h3><?= $lang["full_calendar_view"] ?? "Full Calendar View" ?></h3>

    <a href="/admin/calendar.php">
        <button class="btn-go" style="width:100%;">
            <?= $lang["open_calendar"] ?? "Open Calendar" ?>
        </button>
    </a>
</div>

<!-- EXCEL EXPORT -->
<div class="tab-content" id="excel">

    <h3><?= $lang["excel_export"] ?? "Excel Export (PhpSpreadsheet)" ?></h3>

    <!-- GESAMTEXPORT -->
    <div class="export-box">

        <h4>📊 <?= $lang["complete_export"] ?? "Complete Export" ?></h4>

        <form action="/admin/export_excel_all.php" method="GET">

            <div style="margin-bottom:10px;">
                <label>
                    <strong><?= $lang["export_from"] ?? "From" ?>:</strong>
                </label>
                <input
                    type="date"
                    name="from"
                    value="<?= date('Y-m-01') ?>"
                    style="width:100%;padding:10px;">
            </div>

            <div style="margin-bottom:10px;">
                <label>
                    <strong><?= $lang["export_to"] ?? "To" ?>:</strong>
                </label>
                <input
                    type="date"
                    name="to"
                    value="<?= date('Y-m-d') ?>"
                    style="width:100%;padding:10px;">
            </div>

            <button type="submit" class="btn-come" style="width:100%;">
                📥 <?= $lang["export_complete_list"] ?? "Export Complete List" ?>
            </button>

        </form>

    </div>

    <!-- EINZELNE MITARBEITER -->
    <?php foreach ($employees as $e): ?>
        <div class="export-box">

            <b><?= htmlspecialchars($e['name']) ?></b>
            <br><br>

            <a href="/admin/export_excel.php?employee_id=<?= $e['id'] ?>">
                <button class="btn-come" style="width:100%;">
                    ⬇ <?= $lang["full_export"] ?? "Full Export (.xlsx)" ?>
                </button>
            </a>

            <a href="/admin/export_excel.php?employee_id=<?= $e['id'] ?>&mode=month">
                <button class="btn-go" style="width:100%;margin-top:8px;">
                    📅 <?= $lang["current_month"] ?? "Current Month" ?>
                </button>
            </a>

        </div>
    <?php endforeach; ?>

</div>

<!-- REPORT -->
<div class="tab-content" id="reports">
    <h3><?= $lang["reports"] ?? "Reports" ?></h3>

    <a href="/admin/report.php">
        <button class="btn-come" style="width:100%;">
            <?= $lang["open_report_page"] ?? "Open Report Page" ?>
        </button>
    </a>
</div>

<!-- LOGOUT -->
<div class="tab-content" id="logout">
    <h3><?= $lang["logout"] ?? "Logout" ?></h3>

    <a href="/admin/logout.php">
        <button class="btn-go" style="width:100%;">
            <?= $lang["logout"] ?? "Logout" ?>
        </button>
    </a>
</div>

<script>
const LANG = <?= json_encode($lang, JSON_UNESCAPED_UNICODE) ?>;

const tabs = document.querySelectorAll('.tab');
const contents = document.querySelectorAll('.tab-content');

tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        contents.forEach(c => c.classList.remove('active'));
        document.getElementById(tab.dataset.tab).classList.add('active');
    });
});
</script>

</body>
</html>