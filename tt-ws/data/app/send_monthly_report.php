<?php
require_once "config/database.php";
require_once "config/mail.php";
require_once "includes/lang.php";
require_once "vendor/autoload.php";
require_once "includes/auth.php";

requireAdmin();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$lang = loadLang();
$mailConfig = require __DIR__ . "/config/mail.php";

/* Fetch all employees */
$employees = $pdo->query("SELECT * FROM employees ORDER BY name")->fetchAll();

/* Determine previous month */
$prevMonth = date('m', strtotime('first day of last month'));
$prevYear  = date('Y', strtotime('first day of last month'));
$monthLabel = date('F Y', strtotime('first day of last month'));

/* Create Excel file */
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

/* TITLE */
$sheet->setCellValue(
    'A1',
    ($lang["monthly_report"] ?? "Monthly Report") . ": " . $monthLabel
);
$sheet->mergeCells('A1:D1');
$sheet->getStyle('A1')->getFont()->setBold(true);

/* HEADER */
$sheet->setCellValue('A2', $lang["employees"] ?? "Employee");
$sheet->setCellValue('B2', $lang["date"] ?? "Date");
$sheet->setCellValue('C2', $lang["action"] ?? "Action");
$sheet->setCellValue('D2', $lang["time"] ?? "Time");
$sheet->getStyle('A2:D2')->getFont()->setBold(true);

$rowNum = 3;

/* DATA */
foreach ($employees as $e) {
    $stmt = $pdo->prepare("
        SELECT * FROM time_entries
        WHERE employee_id=? AND MONTH(entry_time)=? AND YEAR(entry_time)=?
        ORDER BY entry_time
    ");
    $stmt->execute([$e['id'], $prevMonth, $prevYear]);
    $entries = $stmt->fetchAll();

    foreach ($entries as $entry) {
        $action = match ($entry['action']) {
            'come' => $lang['come'] ?? 'COME',
            'go'   => $lang['go'] ?? 'GO',
            default => strtoupper($entry['action'])
        };

        $sheet->setCellValue('A'.$rowNum, $e['name']);
        $sheet->setCellValue('B'.$rowNum, date("Y-m-d", strtotime($entry['entry_time'])));
        $sheet->setCellValue('C'.$rowNum, $action);
        $sheet->setCellValue('D'.$rowNum, date("H:i", strtotime($entry['entry_time'])));

        $rowNum++;
    }
}

/* AUTO SIZE */
foreach (range('A', 'D') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

/* SAVE FILE */
$filename = sys_get_temp_dir() . "/monthly_report_" . date('Y_m', strtotime('first day of last month')) . ".xlsx";
$writer = new Xlsx($spreadsheet);
$writer->save($filename);

/* EMAIL */
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $mailConfig['smtp']['host'];
    $mail->SMTPAuth   = $mailConfig['smtp']['auth'];
    $mail->Port       = $mailConfig['smtp']['port'];
    $mail->SMTPSecure = $mailConfig['smtp']['secure'];
    $mail->SMTPAutoTLS = $mailConfig['smtp']['autotls'];

    if ($mailConfig['smtp']['username']) $mail->Username = $mailConfig['smtp']['username'];
    if ($mailConfig['smtp']['password']) $mail->Password = $mailConfig['smtp']['password'];

    $mail->setFrom($mailConfig['from']['address'], $mailConfig['from']['name']);

    foreach ($mailConfig['recipients'] as $r) {
        $mail->addAddress($r['email'], $r['name']);
    }

    $mail->addAttachment($filename, basename($filename));

    $mail->isHTML(true);
    $mail->Subject = ($lang["monthly_report"] ?? "Monthly Report") . " - " . $monthLabel;
    $mail->Body = "<p>" . ($lang["monthly_report_attached"] ?? "Attached is the monthly attendance report for all employees.") . "</p>";

    $mail->send();
    echo $lang["email_sent_success"] ?? "Monthly report sent successfully.";

} catch (Exception $e) {
    echo ($lang["email_error"] ?? "Error sending email") . ": " . $mail->ErrorInfo;
}
