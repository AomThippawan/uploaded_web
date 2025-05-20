<!-- <?php
session_start();
include 'config_db.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$file_id = isset($_GET['file_id']) ? intval($_GET['file_id']) : 0;
if (!$file_id) {
    die("ไม่พบไฟล์");
}

// ดึงข้อมูลไฟล์
$file_res = $conn->query("SELECT * FROM uploaded_file WHERE id = $file_id");
if ($file_res->num_rows === 0) {
    die("ไม่พบไฟล์นี้");
}
$file = $file_res->fetch_assoc();

// ดึงรายชื่อผู้ใช้ทั้งหมด (ไม่รวม admin)
$users_res = $conn->query("SELECT username, name FROM user_his WHERE role != 'admin'");

// ดึงรายชื่อผู้ใช้ที่ได้รับสิทธิ์ดูไฟล์นี้
$access_res = $conn->query("SELECT username FROM file_access WHERE file_id = $file_id");
$access_users = [];
while ($row = $access_res->fetch_assoc()) {
    $access_users[] = $row['username'];
}

// เมื่อ submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed_users = $_POST['allowed_users'] ?? [];

    // ลบสิทธิ์เดิมทั้งหมดของไฟล์นี้
    $conn->query("DELETE FROM file_access WHERE file_id = $file_id");

    // เพิ่มสิทธิ์ใหม่
    $stmt = $conn->prepare("INSERT INTO file_access (file_id, username) VALUES (?, ?)");
    foreach ($allowed_users as $user) {
        $stmt->bind_param("is", $file_id, $user);
        $stmt->execute();
    }

    header("Location: manage_file_users.php?file_id=$file_id&success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <title>จัดการสิทธิ์ผู้ใช้สำหรับไฟล์ <?= htmlspecialchars($file['filename']) ?></title>
    <style>
        .checkbox-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h2>จัดการสิทธิ์ผู้ใช้สำหรับไฟล์: <?= htmlspecialchars($file['filename']) ?></h2>

    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">บันทึกข้อมูลเรียบร้อยแล้ว</p>
    <?php endif; ?>

    <form method="post">
        <div class="checkbox-list">
            <?php while ($user = $users_res->fetch_assoc()): ?>
                <label>
                    <input type="checkbox" name="allowed_users[]" value="<?= htmlspecialchars($user['username']) ?>"
                        <?= in_array($user['username'], $access_users) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['username']) ?>)
                </label>
            <?php endwhile; ?>
        </div>
        <br>
        <button type="submit">บันทึกสิทธิ์</button>
    </form>

    <p><a href="view_files.php">← กลับไปหน้าไฟล์</a></p>
</body>
</html> -->
