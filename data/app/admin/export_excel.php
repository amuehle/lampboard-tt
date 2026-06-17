<?php
require_once "../config/database.php";
require_once "../includes/auth.php";
require_once "../includes/lang.php";
require_once "../includes/time_rounding.php";
require_once "../vendor/autoload.php";

$lang = loadLang();
requireAdmin();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$employee_id = (int)($_GET['employee_id'] ?? 0);
$mode = $_GET['mode'] ?? 'all';

if (!$employee_id) {
    die($lang['no_employee'] ?? "No employee selected");
}

/* EMPLOYEE */
$stmtEmp = $pdo->prepare("SELECT * FROM employees WHERE id=?");
$stmtEmp->execute([$employee_id]);
$employee = $stmtEmp->fetch();

$employeeName = $employee['name'] ?? 'Employee';

/* DATA */
if ($mode === 'month') {
    $stmt = $pdo->prepare("
        SELECT * FROM time_entries
        WHERE employee_id = ?
        AND MONTH(entry_time) = MONTH(NOW())
        AND YEAR(entry_time) = YEAR(NOW())
        ORDER BY entry_time
    ");
    $stmt->execute([$employee_id]);
} else {
    $stmt = $pdo->prepare("
        SELECT * FROM time_entries
        WHERE employee_id = ?
        ORDER BY entry_time
    ");
    $stmt->execute([$employee_id]);
}

$data = $stmt->fetchAll();

/* SPREADSHEET */
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', "Time Report - " . $employeeName);
$sheet->mergeCells('A1:C1');

$sheet->setCellValue('A2', $lang['date'] ?? 'Date');
$sheet->setCellValue('B2', $lang['action'] ?? 'Action');
$sheet->setCellValue('C2', $lang['time'] ?? 'Time');

$sheet->getStyle('A2:C2')->getFont()->setBold(true);

$row = 3;

$roundedTotal = 0;
$lastCome = null;

foreach ($data as $r) {

    $action = $r['action'];

    $label = $action === 'come'
        ? ($lang['come'] ?? 'COME')
        : ($lang['go'] ?? 'GO');

    $roundedTime = applyTimeRules($r['entry_time'], $action, $employee);

    $sheet->setCellValue('A'.$row, date("Y-m-d", strtotime($roundedTime)));
    $sheet->setCellValue('B'.$row, $label);
    $sheet->setCellValue('C'.$row, date("H:i:s", strtotime($roundedTime)));

    $row++;

    /* ===== ROUNDED TOTAL ===== */
    $time = new DateTime($r['entry_time']);

    if ($action === 'come') {
        $lastCome = new DateTime(
            applyTimeRules($time->format('Y-m-d H:i:s'), 'come', $employee)
        );
    }

    if ($action === 'go' && $lastCome) {

        $go = new DateTime(
            applyTimeRules($time->format('Y-m-d H:i:s'), 'go', $employee)
        );

        $diff = ($go->getTimestamp() - $lastCome->getTimestamp()) / 60;

        if ($diff > 0) {
            $roundedTotal += $diff;
        }

        $lastCome = null;
    }
}

/* TOTAL ROW */
$h = floor($roundedTotal / 60);
$m = $roundedTotal % 60;

$sheet->setCellValue('A'.$row, "TOTAL");
$sheet->setCellValue('C'.$row, sprintf('%02d:%02d', $h, $m));

foreach (range('A','C') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$filename = "time_report_" . $employeeName . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;