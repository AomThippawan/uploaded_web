<?php
session_start();
require('config_db.php');

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$file_id = $_GET['file_id'] ?? null;
if (!$file_id || !is_numeric($file_id)) {
    die("❌ ไม่พบรหัสไฟล์ที่ถูกต้อง");
}

$stmt = $conn->prepare("SELECT * FROM goal WHERE uploaded_file_id = ?");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียด Goal</title>
    <link rel="stylesheet" href="../css/view_file.css">
    <style>
        .btn { padding: 6px 12px; text-decoration: none; margin: 2px; display: inline-block; }
        .add-btn { background-color: #28a745; color: white; }
        .delete-btn { background-color: #dc3545; color: white; }
        .update { background-color: #007bff; color: white; }
        .goal-set { border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; background: #f9f9f9; }
        .modal { display: none; position: fixed; z-index: 999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fff; margin: 10% auto; padding: 20px; width: 500px; border-radius: 8px; }
        .close { float: right; cursor: pointer; font-size: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h2>รายละเอียด Goal ของไฟล์ ID: <?= htmlspecialchars($file_id) ?></h2>

    <!-- ปุ่มเพิ่ม Goal -->
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <button class="btn add-btn" onclick="openModal()">➕ เพิ่ม Goal</button>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ชื่อ Goal</th>
                    <th>รายละเอียด</th>
                    <th>วันที่สร้าง</th>
                    <th>เดดไลน์</th>
                    <th>ความคืบหน้า</th>
                    <th>สถานะ</th>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <th>อัปเดต</th>
                        <th>ลบ</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['goal_name']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                        <td><?= htmlspecialchars($row['deadline']) ?></td>
                        <td><?= htmlspecialchars($row['target_value']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <td>
                                <a class="btn update" href="update_goal.php?goal_id=<?= urlencode($row['id']) ?>">อัปเดต</a>
                            </td>
                            <td>
                                <a class="btn delete-btn" href="delete_goal.php?goal_id=<?= urlencode($row['id']) ?>" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบ Goal นี้?')">🗑️ ลบ</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>⚠️ ไม่พบ Goal ที่เกี่ยวข้องกับไฟล์นี้</p>
    <?php endif; ?>

    <p>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a class="back-link" href="view_files.php">← กลับ</a>
        <?php else: ?>
            <a class="back-link" href="user.php">← กลับ</a>
        <?php endif; ?>
    </p>
</div>

<!-- Modal เพิ่ม Goal -->
<?php if ($_SESSION['role'] === 'admin'): ?>
<div id="goalModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>เพิ่ม Goal ใหม่</h3>
        <form action="add_goal.php" method="POST">
            <input type="hidden" name="uploaded_file_id" value="<?= htmlspecialchars($file_id) ?>">

            <div class="goal-set">
                <label>หัวข้อ Goal:</label><br>
                <input type="text" name="goal_name" required><br><br>

                <label>รายละเอียด Goal:</label><br>
                <textarea name="goal_description" rows="3" required></textarea><br><br>

                <label>วันครบกำหนด (Deadline):</label><br>
                <input type="date" name="goal_deadline" required><br><br>

                <label>สถานะ Goal:</label><br>
                <select name="goal_status" required>
                    <option value="pending">รอดำเนินการ</option>
                    <option value="in_progress">กำลังดำเนินการ</option>
                    <option value="completed">เสร็จสิ้นแล้ว</option>
                    <option value="on_hold">พักไว้ก่อน</option>
                    <option value="cancelled">ยกเลิก</option>
                </select><br><br>
            </div>

            <button type="submit" class="btn add-btn">บันทึก Goal</button>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
    function openModal() {
        document.getElementById("goalModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("goalModal").style.display = "none";
    }

    window.onclick = function(event) {
        const modal = document.getElementById("goalModal");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>
</body>
</html>
