<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require_once 'config_db.php';

$sql = "SELECT id, name, username, role FROM user_his";
$result = $conn->query($sql);

if (!$result) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูลผู้ใช้: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการผู้ใช้</title>
    <link rel="stylesheet" href="../css/manage_user.css">
</head>
<body>

<div class="header">
    <h2>จัดการผู้ใช้</h2>
</div>

<div class="content">
    <div style="margin-bottom: 15px; display: flex; justify-content:space-between; gap: 10px;">
    <a href="admin.php" style="
        text-decoration: none;
        color: white;
        background-color: #4caf50;
        padding: 8px 12px;
        border-radius: 4px;
        font-weight: bold;
        display: inline-block;
    ">←  ย้อนกลับ</a>

    <a href="add_user.php" style="
        text-decoration: none;
        color: white;
        background-color: #4caf50;
        padding: 8px 12px;
        border-radius: 4px;
        font-weight: bold;
        display: inline-block;
    ">+ เพิ่มผู้ใช้ใหม่</a>
</div>

        
    <table>
        <thead>
            <tr>
                <th>ชื่อ</th>
                <th>ชื่อผู้ใช้</th>
                <th>role</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                    <td class="actions">
                        <a class="edit" href="edit_user.php?id=<?php echo $row['id']; ?>">แก้ไข</a>
                        <a class="delete" href="delete_user.php?id=<?php echo $row['id']; ?>" onclick="return confirm('ยืนยันการลบผู้ใช้นี้?');">ลบ</a>
                    </td>
                </tr>
                
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
