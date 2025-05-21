<?php
session_start();
include 'config_db.php';

if (!isset($_SESSION['username'])) {
    echo "unauthorized";
    exit;
}

$id = $_POST['id'] ?? '';
$column = $_POST['column'] ?? '';
$value = $_POST['value'] ?? '';
$table = $_POST['table'] ?? '';

if (!$id || !$column || !$table) {
    echo "missing parameters";
    exit;
}

$column = str_replace("`", "", $column);
$table = preg_replace('/[^\p{L}\p{M}\p{N}_]/u', '', $table);

// ตรวจสอบสิทธิ์
$stmt = $conn->prepare("SELECT allowed_users FROM uploaded_file WHERE REPLACE(filename, '.xlsx', '') = ?");
$stmt->bind_param("s", $table);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo "not found";
    exit;
}
$row = $res->fetch_assoc();
$allowed_users = array_map('trim', explode(',', $row['allowed_users']));
if ($_SESSION['role'] !== 'admin' && !in_array($_SESSION['username'], $allowed_users)) {
    echo "forbidden";
    exit;
}

$stmt = $conn->prepare("UPDATE `$table` SET `$column` = ? WHERE id = ?");
$stmt->bind_param("si", $value, $id);
if ($stmt->execute()) {
    echo "success";
} else {
    echo "error: " . $stmt->error;
}
