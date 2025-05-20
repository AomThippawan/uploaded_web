<?php
session_start();
include 'config_db.php';

// ตรวจสอบสิทธิ์ admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // ดึงข้อมูลไฟล์จากฐานข้อมูล
    $stmt = $conn->prepare("SELECT filepath FROM uploaded_file WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($filepath);
    $stmt->fetch();
    $stmt->close();

    if ($filepath && file_exists($filepath)) {
        unlink($filepath); // ลบไฟล์จากเซิร์ฟเวอร์

        // แปลงชื่อไฟล์ (เช่น uploads/student.xlsx → student)
        $filename = pathinfo($filepath, PATHINFO_FILENAME);
        $tablename = preg_replace('/[^a-zA-Z0-9_]/', '', $filename); // กัน SQL Injection
        
        // ลบตารางในฐานข้อมูล
        $dropSql = "DROP TABLE IF EXISTS `$tablename`";
        if (!$conn->query($dropSql)) {
            error_log("ไม่สามารถลบตาราง $tablename: " . $conn->error);
        }
    }

    // ลบข้อมูลจากตาราง uploaded_file
    $stmt = $conn->prepare("DELETE FROM uploaded_file WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: view_files.php"); // กลับไปหน้าเดิม
    exit;
} else {
    echo "ไม่พบไฟล์ที่ต้องการลบ";
}
?>
