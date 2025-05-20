<?php
session_start();
include 'config_db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// ดึงรายชื่อผู้ใช้ที่ไม่ใช่แอดมิน
$users = $conn->query("SELECT username, name FROM user_his WHERE role != 'admin'");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>อัปโหลดไฟล์ Excel</title>
    <link rel="stylesheet" href="../css/upload.css">
    <style>
        .checkbox-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 5px;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
        }
        .checkbox-list label {
            display: flex;
            align-items: center;
            gap: 5px;
            text-align: left;
            justify-content: flex-start;
        }
    </style>
</head>
<body>
    <h2>อัปโหลดไฟล์ .xlsx</h2>
    <form action="upload_process.php" method="POST" enctype="multipart/form-data">
        <label>เลือกไฟล์:</label>
        <input type="file" name="excel_file" accept=".xlsx" required><br><br>

        <label>เลือกผู้ใช้ที่สามารถดูไฟล์นี้ได้:</label><br>
        <div class="checkbox-list">
            <?php while ($row = $users->fetch_assoc()): ?>
                <label>
                    <input type="checkbox" name="allowed_users[]" value="<?= htmlspecialchars($row['username']) ?>">
                    <?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['username']) ?>)
                </label>
            <?php endwhile; ?>
        </div><br>

        <input type="submit" value="อัปโหลด">
    </form>
</body>
</html>
