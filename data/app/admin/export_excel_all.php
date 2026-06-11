<?php
require_once "../config/database.php";
require_once "../includes/auth.php";
require_once "../includes/lang.php";
require_once "../vendor/autoload.php";

$lang = loadLang();
requireAdmin();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-t');

$stmt = $pdo->prepare("
    SELECT
        e.name,
        t.action,
        t.entry_time
    FROM time_entries t
    JOIN employees e ON e.id = t.employee_id
    WHERE DATE(t.entry_time) BETWEEN ? AND ?
    ORDER BY e.name, t.entry_time
");

$stmt->execute([$from, $to]);

$data = $stmt->fetchAll();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

/* TITLE */
$sheet->setCellValue(
    'A1',
    ($lang['complete_export'] ?? 'Complete Export')
    . " ({$from} - {$to})"
);

$sheet->mergeCells('A1:D1');

/* HEADER */
$sheet->setCellValue('A2', $lang['employees'] ?? 'Employees');
$sheet->setCellValue('B2', $lang['date'] ?? 'Date');
$sheet->setCellValue('C2', $lang['action'] ?? 'Action');
$sheet->setCellValue('D2', $lang['time'] ?? 'Time');

$sheet->getStyle('A2:D2')->getFont()->setBold(true);

$row = 3;

foreach ($data as $entry) {

    $action = $entry['action'] === 'come'
        ? ($lang['come'] ?? 'COME')
        : ($lang['go'] ?? 'GO');

    $sheet->setCellValue('A'.$row, $entry['name']);
    $sheet->setCellValue('B'.$row, date('Y-m-d', strtotime($entry['entry_time'])));
    $sheet->setCellValue('C'.$row, $action);
    $sheet->setCellValue('D'.$row, date('H:i:s', strtotime($entry['entry_time'])));

    $row++;
}

foreach (range('A','D') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$filename = "complete_export_{$from}_{$to}.xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;