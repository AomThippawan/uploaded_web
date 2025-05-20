<?php
require_once 'config_db.php';

if (!isset($_GET['id'])) {
    die("ไม่พบผู้ใช้ที่ต้องการลบ");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM user_his WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: manage_user.php");
    exit;
} else {
    echo "เกิดข้อผิดพลาดในการลบผู้ใช้: " . $stmt->error;
}
?>
    