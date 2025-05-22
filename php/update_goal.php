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

// ถ้า POST ให้ทำการอัปเดต
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $goal_name = $_POST['goal_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $target_value = $_POST['target_value'] ?? 0;
    $status = $_POST['status'] ?? 'pending';
    $file_id = $_POST['file_id'] ?? 0;

    $stmt = $conn->prepare("UPDATE goal SET goal_name=?, description=?, target_value=?, status=? WHERE id=?");
    $stmt->bind_param("ssdsi", $goal_name, $description, $target_value, $status, $goal_id);

    if ($stmt->execute()) {
        header("Location: goal_details.php?file_id=" . $file_id);
        exit;
    } else {
        $error = "❌ เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
    }
}

// โหลดข้อมูล goal ปัจจุบัน
$stmt = $conn->prepare("SELECT * FROM goal WHERE id = ?");
$stmt->bind_param("i", $goal_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("⚠️ ไม่พบ Goal นี้");
}

$goal = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>อัปเดต Goal</title>
    <link rel="stylesheet" href="../css/up_goal.css">
</head>
<body>
    <div class="container">
        <h2>อัปเดต Goal</h2>

        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <form method="POST">
            <input type="hidden" name="file_id" value="<?= htmlspecialchars($goal['uploaded_file_id']) ?>">

            <label>ชื่อ Goal:</label>
            <input type="text" name="goal_name" required value="<?= htmlspecialchars($goal['goal_name']) ?>">

            <label>รายละเอียด:</label>
            <textarea name="description" rows="3"><?= htmlspecialchars($goal['description']) ?></textarea>

            <label for="target_value">ความคืบหน้า (ค่าเป้าหมาย):</label>
            <input type="number" name="target_value" step="0.01" required value="<?= htmlspecialchars($goal['target_value']) ?>">

            <label>สถานะ:</label>
            <select name="status">
                <option value="pending" <?= $goal['status'] === 'pending' ? 'selected' : '' ?>>รอดำเนินการ</option>
                <option value="completed" <?= $goal['status'] === 'completed' ? 'selected' : '' ?>>สำเร็จแล้ว</option>
            </select>

            <button type="submit">บันทึกการอัปเดต</button>
            <a href="goal_details.php?file_id=<?= htmlspecialchars($goal['uploaded_file_id']) ?>" class="btn-cancel">ยกเลิก</a>
        </form>
    </div>
</body>
</html>
