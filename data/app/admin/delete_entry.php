<?php
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$id = (int)($_POST['id'] ?? 0);

if (!$id) {
    http_response_code(400);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM time_entries WHERE id=?");
$stmt->execute([$id]);

echo json_encode(["success" => true]);