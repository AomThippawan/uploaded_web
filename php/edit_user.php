<?php
require_once 'config_db.php';

if (!isset($_GET['id'])) {
    die("ไม่พบผู้ใช้ที่ต้องการแก้ไข");
}

$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE user_his SET name = ?, username = ?, role = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $username, $role, $id);

    if ($stmt->execute()) {
        header("Location: manage_user.php");
        exit;
    } else {
        echo "เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . $stmt->error;
    }
}

$stmt = $conn->prepare("SELECT name, username, role FROM user_his WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("ไม่พบข้อมูลผู้ใช้");
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขผู้ใช้</title>
    <link rel="stylesheet" href="../css/add.css">
</head>
<body>
    <div class="container">
        <h2>แก้ไขข้อมูลผู้ใช้</h2>
        <form method="post" class="form-box">
            <label>ชื่อ:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

            <label>ชื่อผู้ใช้:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label>สิทธิ์:</label>
            <select name="role" required>
                <option value="user" <?php if ($user['role'] === 'user') echo 'selected'; ?>>user</option>
                <option value="admin" <?php if ($user['role'] === 'admin') echo 'selected'; ?>>admin</option>
            </select>

            <div class="button-group">
                <button type="submit">บันทึก</button>
                <a href="manage_user.php" class="cancel-btn">ยกเลิก</a>
            </div>
        </form>
    </div>
</body>
</html>
