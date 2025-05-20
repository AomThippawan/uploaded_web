<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once 'config_db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if ($name === '' || $username === '' || $password === '') {
        $errors[] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } else {
        $stmt = $conn->prepare("SELECT id FROM user_his WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "ชื่อผู้ใช้นี้ถูกใช้แล้ว";
        } else {
            $stmt = $conn->prepare("INSERT INTO user_his (name, username, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $username, $password, $role);

            if ($stmt->execute()) {
                header("Location: manage_user.php");
                exit;
            } else {
                $errors[] = "เกิดข้อผิดพลาดในการเพิ่มผู้ใช้: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มผู้ใช้</title>
    <link rel="stylesheet" href="../css/add.css">
</head>
<body>
    <div class="container">
        <h2>เพิ่มผู้ใช้ใหม่</h2>

        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="form-box">
            <label>ชื่อ:</label>
            <input type="text" name="name" required>

            <label>ชื่อผู้ใช้:</label>
            <input type="text" name="username" required>

            <label>รหัสผ่าน:</label>
            <input type="text" name="password" required>

            <label>สิทธิ์:</label>
            <select name="role" required>
                <option value="user">user</option>
                <option value="admin">admin</option>
            </select>

            <div class="button-group">
                <button type="submit">เพิ่มผู้ใช้</button>
                <a href="manage_user.php" class="cancel-btn">ยกเลิก</a>
            </div>
        </form>
    </div>
</body>
</html>
