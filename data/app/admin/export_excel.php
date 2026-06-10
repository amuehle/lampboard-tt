<?php
require_once "../config/database.php";
require_once "../includes/auth.php";
require_once "../includes/lang.php";

$lang = loadLang();
requireAdmin();

require_once "../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$employee_id = (int)($_GET['employee_id'] ?? 0);
$mode = $_GET['mode'] ?? 'all';

if (!$employee_id) {
    die($lang['no_booking'] ?? "No employee selected");
}

/* FETCH EMPLOYEE */
$stmtEmp = $pdo->prepare("SELECT name FROM employees WHERE id=?");
$stmtEmp->execute([$employee_id]);
$employee = $stmtEmp->fetch();
$employeeName = $employee['name'] ?? 'Employee';

/* QUERY DATA */
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

/* CREATE SPREADSHEET */
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

/* TITLE */
$sheet->setCellValue('A1', "Time Report - " . $employeeName);
$sheet->mergeCells('A1:C1');

/* HEADER */
$sheet->setCellValue('A2', $lang['date'] ?? 'Date');
$sheet->setCellValue('B2', $lang['action'] ?? 'Action');
$sheet->setCellValue('C2', $lang['time'] ?? 'Time');

/* STYLE HEADER */
$sheet->getStyle('A2:C2')->getFont()->setBold(true);

/* DATA */
$rowNum = 3;

foreach ($data as $row) {
    $sheet->setCellValue('A' . $rowNum, date("Y-m-d", strtotime($row['entry_time'])));

    // Translate COME / GO
    $action = '';
    if ($row['action'] === 'come') {
        $action = $lang['come'] ?? 'COME';
    } elseif ($row['action'] === 'go') {
        $action = $lang['go'] ?? 'GO';
    } else {
        $action = $lang['no_booking'] ?? strtoupper($row['action']);
    }

    $sheet->setCellValue('B' . $rowNum, $action);
    $sheet->setCellValue('C' . $rowNum, date("H:i:s", strtotime($row['entry_time'])));
    $rowNum++;
}

/* AUTO SIZE COLUMNS */
foreach (range('A', 'C') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

/* OUTPUT FILE */
$filename = "time_report_" . $employeeName . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
