<?php
session_start();
require('config_db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$goal_id = $_GET['goal_id'] ?? null;
if (!$goal_id || !is_numeric($goal_id)) {
    die("❌ ไม่พบรหัส Goal ที่ถูกต้อง");
}

// ดึง uploaded_file_id เพื่อ redirect กลับหลังลบ
$stmt_select = $conn->prepare("SELECT uploaded_file_id FROM goal WHERE id = ?");
$stmt_select->bind_param("i", $goal_id);
$stmt_select->execute();
$res = $stmt_select->get_result();
if ($res->num_rows === 0) {
    die("⚠️ ไม่พบ Goal ที่ต้องการลบ");
}
$row = $res->fetch_assoc();
$file_id = $row['uploaded_file_id'];

// ลบจริง
$stmt_delete = $conn->prepare("DELETE FROM goal WHERE id = ?");
$stmt_delete->bind_param("i", $goal_id);
$stmt_delete->execute();

header("Location: goal_details.php?file_id=" . urlencode($file_id));
exit;
