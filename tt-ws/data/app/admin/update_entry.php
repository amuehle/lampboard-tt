<?php
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';
$time = $_POST['time'] ?? '';

if (!$id || !in_array($action, ['come','go']) || !strtotime($time)) {
    http_response_code(400);
    exit;
}

$stmt = $pdo->prepare("
    UPDATE time_entries
    SET action=?, entry_time=?
    WHERE id=?
");

$stmt->execute([$action, $time, $id]);

echo json_encode(["success" => true]);
