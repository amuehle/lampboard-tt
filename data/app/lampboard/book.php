<?php
require_once "../config/database.php";

date_default_timezone_set('Europe/Vienna');

$employee_id = (int)$_POST['employee_id'];
$action = $_POST['action'] ?? '';

if (!in_array($action, ['come', 'go'])) {
    http_response_code(400);
    exit;
}

$now = date("Y-m-d H:i:s");

$stmt = $pdo->prepare("
    INSERT INTO time_entries (employee_id, action, entry_time)
    VALUES (?, ?, ?)
");
$stmt->execute([$employee_id, $action, $now]);

echo json_encode(["success" => true]);
