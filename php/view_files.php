<?php
session_start();
include 'config_db.php';

// เช็คว่าเป็น admin หรือไม่
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// ดึงข้อมูลไฟล์ที่อัปโหลดจากฐานข้อมูล
$sql = "SELECT * FROM uploaded_file ORDER BY uploaded_at DESC";
$result = $conn->query($sql);

if (isset($_POST['delete_all'])) {
    $getFiles = $conn->query("SELECT filepath, filename FROM uploaded_file");
    while ($file = $getFiles->fetch_assoc()) {
        if (file_exists($file['filepath'])) {
            unlink($file['filepath']);
        }
        $tableName = pathinfo($file['filename'], PATHINFO_FILENAME);
        $safeTable = preg_replace('/[^\p{L}\p{M}\p{N}_]/u', '', $tableName);
        if ($safeTable !== '') {
            $conn->query("DROP TABLE IF EXISTS `$safeTable`");
        }
    }

    $conn->query("DELETE FROM uploaded_file");

    header("Location: view_files.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ไฟล์ที่อัปโหลดแล้ว</title>
    <link rel="stylesheet" href="../css/view_file.css">
</head>

<body>
    <div class="container">
        <h2>ไฟล์ที่อัปโหลดแล้ว</h2>
        <div class="addfile-btn" style="display: flex; justify-content: flex-end; width: 100%; margin-top: 20px;text-decoration-line: none;">
            <a href="upload_form.php" class="menu-item" style="text-decoration-line: none; padding: 10px; background-color:rgb(49, 141, 55); border-radius: 6px; color: white;">📤 อัปโหลดไฟล์ .xlsx</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ชื่อไฟล์</th>
                    <!-- <th>ที่ตั้งไฟล์</th> -->
                    <th>อัปโหลดโดย</th>
                    <th>วันที่อัปโหลด</th>
                    <th>Goal</th>
                    <th>แก้ไข</th>
                    <th>ดาวน์โหลด</th>
                    <th>ลบ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $tableName = pathinfo($row['filename'], PATHINFO_FILENAME); // ตัด .xlsx ออก

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['filename']) . "</td>";
                        // echo "<td>" . htmlspecialchars($row['filepath']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['uploaded_by']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['uploaded_at']) . "</td>";
                        
                        echo "<td><a class='btn goal' href='goal_details.php?file_id=" . $row['id'] . "'>ดู Goal</a></td>";

                        // ปุ่มดูข้อมูล พร้อมส่ง table name และ timestamp กันแคช
                        echo "<td>
                                <form method='get' action='view_table_admin.php'>
                                    <input type='hidden' name='table' value='" . htmlspecialchars($tableName) . "'>
                                    <input type='hidden' name='t' value='" . time() . "'>
                                    <button type='submit' class='btn view'>แก้ไข</button>
                                </form>
                              </td>";

                        echo "<td><a class='btn download' href='" . htmlspecialchars($row['filepath']) . "' download>ดาวน์โหลด</a></td>";

                        // // ปุ่มจัดการสิทธิ์ผู้ใช้
                        // echo "<td><a class='btn manage' href='manage_file_users.php?file_id=" . $row['id'] . "'>👥</a></td>";

                        // ปุ่มลบ
                        echo "<td><a class='btn delete' href='delete_file.php?id=" . $row['id'] . "' onclick=\"return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบไฟล์นี้?');\">ลบ</a></td>";

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>ยังไม่มีไฟล์ที่อัปโหลด</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <!-- ลบทั้งหมด -->
        <div style="display: flex; justify-content: flex-end; width: 100%; margin-top: 20px;">
            <form method="post" onsubmit="return confirm('ต้องการลบไฟล์ทั้งหมดหรือไม่?');">
                <button type="submit" name="delete_all" class="btn delete" style="padding: 10px;">🗑️ ลบทั้งหมด</button>
            </form>
        </div>

        <p><a class="back-link" href="admin.php">← กลับสู่หน้าแอดมิน</a></p>


    </div>
</body>

</html>