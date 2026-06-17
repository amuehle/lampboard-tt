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

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-t');

$stmt = $pdo->prepare("
    SELECT t.*, e.name, e.excel_rounding_enabled, e.janitor_exception
    FROM time_entries t
    JOIN employees e ON e.id = t.employee_id
    WHERE DATE(t.entry_time) BETWEEN ? AND ?
    ORDER BY e.name, t.entry_time
");

$stmt->execute([$from, $to]);
$data = $stmt->fetchAll();

/* GROUP BY EMPLOYEE */
$grouped = [];
foreach ($data as $r) {
    $grouped[$r['name']][] = $r;
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1',
    ($lang['complete_export'] ?? 'Complete Export') . " ({$from} - {$to})"
);
$sheet->mergeCells('A1:D1');

$sheet->setCellValue('A2', $lang['employees'] ?? 'Employees');
$sheet->setCellValue('B2', $lang['date'] ?? 'Date');
$sheet->setCellValue('C2', $lang['action'] ?? 'Action');
$sheet->setCellValue('D2', $lang['time'] ?? 'Time');

$sheet->getStyle('A2:D2')->getFont()->setBold(true);

$row = 3;

/* LOOP EMPLOYEES */
foreach ($grouped as $name => $entries) {

    $employee = [
        'excel_rounding_enabled' => $entries[0]['excel_rounding_enabled'],
        'janitor_exception' => $entries[0]['janitor_exception']
    ];

    $roundedTotal = 0;
    $lastCome = null;

    foreach ($entries as $r) {

        $action = $r['action'];

        $label = $action === 'come'
            ? ($lang['come'] ?? 'COME')
            : ($lang['go'] ?? 'GO');

        $rounded = applyTimeRules($r['entry_time'], $action, $employee);

        $sheet->setCellValue('A'.$row, $name);
        $sheet->setCellValue('B'.$row, date('Y-m-d', strtotime($rounded)));
        $sheet->setCellValue('C'.$row, $label);
        $sheet->setCellValue('D'.$row, date('H:i:s', strtotime($rounded)));

        $row++;

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

    $sheet->setCellValue('A'.$row, $name . " - TOTAL");
    $sheet->setCellValue('D'.$row, sprintf('%02d:%02d', $h, $m));

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