<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

include 'config_db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$targetDir = "../uploads/";
if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

if (isset($_FILES['excel_file']) && isset($_POST['allowed_users'])) {
    $file = $_FILES['excel_file'];
    $filename = basename($file['name']);
    $targetPath = $targetDir . $filename;

    if (strtolower(pathinfo($targetPath, PATHINFO_EXTENSION)) !== 'xlsx') {
        die("อนุญาตเฉพาะไฟล์ .xlsx เท่านั้น");
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $allowed_users = implode(',', $_POST['allowed_users']);

        $stmt = $conn->prepare("INSERT INTO uploaded_file (filename, filepath, uploaded_by, allowed_users) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $filename, $targetPath, $_SESSION['username'], $allowed_users);
        $stmt->execute();

        // อ่านไฟล์ Excel
        $spreadsheet = IOFactory::load($targetPath);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();
        $tableName = preg_replace('/[^\p{L}\p{M}\p{N}_]/u', '_', pathinfo($filename, PATHINFO_FILENAME));

        $rawHeaders = $data[0];


        $columns = array_map(function ($col) {
            return "`" . str_replace("`", "", $col) . "` VARCHAR(255)";
        }, $rawHeaders);

        $createQuery = "CREATE TABLE IF NOT EXISTS `$tableName` (id INT AUTO_INCREMENT PRIMARY KEY, " . implode(",", $columns) . ")";
        $conn->query($createQuery);

        for ($i = 1; $i < count($data); $i++) {
            $escapedCols = array_map(function ($col) {
                return "`" . str_replace("`", "", $col) . "`";
            }, $rawHeaders);

            $values = array_map(fn($v) => "'" . $conn->real_escape_string($v) . "'", $data[$i]);

            $insertQuery = "INSERT INTO `$tableName` (" . implode(",", $escapedCols) . ") VALUES (" . implode(",", $values) . ")";
            $conn->query($insertQuery);
        }

        // อ่านข้อมูล Goal จากฟอร์ม
        $uploaded_file_id = $stmt->insert_id;
        $goal_names = $_POST['goal_name'] ?? [];
        $goal_descriptions = $_POST['goal_description'] ?? [];
        $goal_deadlines = $_POST['goal_deadline'] ?? [];
        $goal_statuses = $_POST['goal_status'] ?? [];

        $stmt2 = $conn->prepare("INSERT INTO goal 
            (uploaded_file_id, goal_name, description, deadline, target_value, status, created_by, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

        for ($i = 0; $i < count($goal_names); $i++) {
            $name = trim($goal_names[$i]);
            $desc = trim($goal_descriptions[$i] ?? '');
            $deadline = trim($goal_deadlines[$i] ?? '');
            $statusGoal = trim($goal_statuses[$i] ?? 'pending');
            $targetValue = 0;

            if ($name === '' || $deadline === '') continue;

            $stmt2->bind_param("isssiss", 
                $uploaded_file_id, $name, $desc, $deadline, $targetValue, $statusGoal, $username);
            $stmt2->execute();

        }

        header("Location: view_files.php");
    } else {
        echo "ไม่สามารถอัปโหลดไฟล์ได้";
    }
} else {
    echo "ไม่มีไฟล์หรือยังไม่ได้เลือกผู้ใช้ที่สามารถดูไฟล์";
}
