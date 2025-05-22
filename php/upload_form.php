<?php
session_start();
include 'config_db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
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
    <script>
        function addGoal() {
            const container = document.getElementById('goal-container');

            const div = document.createElement('div');
            div.className = 'goal-set';
            div.innerHTML = `
                <label>หัวข้อ Goal:</label>
                <input type="text" name="goal_name[]" required><br>

                <label>รายละเอียด Goal:</label>
                <textarea name="goal_description[]" rows="2" required></textarea><br>

                <label>วันครบกำหนด (Deadline):</label>
                <input type="date" name="goal_deadline[]" required><br>

                <label>สถานะ Goal:</label>
                <select name="goal_status[]" required>
                    <option value="pending" selected>รอดำเนินการ</option>
                    <option value="in_progress">กำลังดำเนินการ</option>
                    <option value="completed">เสร็จสิ้นแล้ว</option>
                    <option value="on_hold">พักไว้ก่อน</option>
                    <option value="cancelled">ยกเลิก</option>
                </select><br>

                <button type="button" class="remove-goal-btn" onclick="removeGoal(this)">ลบ Goal นี้</button>
            `;
            container.appendChild(div);
        }

        function removeGoal(btn) {
            btn.parentElement.remove();
        }

        window.onload = function() {
            addGoal(); // เพิ่ม goal แรกอัตโนมัติ
        };
    </script>
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

        <fieldset>
            <legend>รายการ Goal</legend>
            <div id="goal-container"></div>
            <button type="button" onclick="addGoal()">➕ เพิ่ม Goal</button>
        </fieldset>

        <input type="submit" value="อัปโหลด">
        <p><a class="back-link" href="admin.php">← กลับสู่หน้าแอดมิน</a></p>
    </form>
</body>
</html>
