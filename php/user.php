<?php
session_start();
include 'config_db.php';

// ตรวจสอบสิทธิ์การเข้าถึง
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}
$username = $_SESSION['username'];
$name = $_SESSION['name'];

// ดึงรายการไฟล์จากฐานข้อมูล
$sql = "SELECT * FROM uploaded_file ORDER BY uploaded_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>หน้าผู้ใช้</title>
    <link rel="stylesheet" href="../css/user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://fontawesome.com/icons/file-excel?f=classic&s=regular">

</head>

<body>
    <div class="header">
        <h2>ยินดีต้อนรับผู้ใช้ <?php echo htmlspecialchars($username); ?></h2>
        <div class="profile-container">
            <div class="profile-icon" onclick="toggleDropdown()">👤</div>
            <div class="dropdown" id="profileDropdown">
                <p><strong>ชื่อ </strong><?php echo htmlspecialchars($name); ?></p>
                <p><strong>ชื่อผู้ใช้</strong> <?php echo htmlspecialchars($username); ?></p>
                <a href="logout.php" class="logout-button">ออกจากระบบ</a>
            </div>
        </div>
    </div>


    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ชื่อ</th>
                    <th>วันที่อัปโหลด</th>
                    <th>ดูข้อมูล</th>
                    <!-- <th>ดาวน์โหลด</th> -->
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $allowed_users = explode(',', $row['allowed_users']);
        if (in_array($username, $allowed_users)) {
            $tableName = pathinfo($row['filename'], PATHINFO_FILENAME);

            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['filename']) . "</td>";
            echo "<td>" . $row['uploaded_at'] . "</td>";
            echo "<td><a href='view_table_user.php?table=" . urlencode($tableName) . "' title='ดูข้อมูล'>ดูข้อมูล</a></td>";
            // echo "<td><a href='" . htmlspecialchars($row['filepath']) . "' download title='ดาวน์โหลดไฟล์'><i class='fa-regular fa-file-excel'></i></a></td>";
            echo "</tr>";
        }
    }
} else {
    echo "<tr><td colspan='4'>ไม่มีไฟล์ที่พร้อมให้ดาวน์โหลด</td></tr>";
}

                ?>
            </tbody>
        </table>
    </div>

    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        window.onclick = function(e) {
            if (!e.target.matches('.profile-icon')) {
                const dropdown = document.getElementById('profileDropdown');
                if (dropdown && dropdown.style.display === 'block') {
                    dropdown.style.display = 'none';
                }
            }
        }
    </script>

</body>

</html>