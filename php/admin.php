<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
$username = $_SESSION['username'];
$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>หน้าแอดมิน</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

    <div class="header">
        <h2>ยินดีต้อนรับแอดมิน <?php echo htmlspecialchars($username); ?></h2>
        <div class="profile-container">
            <div class="profile-icon" onclick="toggleDropdown()">👤</div>
            <div class="dropdown" id="profileDropdown">
                <p><strong>ชื่อ </strong><?php echo htmlspecialchars($name); ?></p>
                <p><strong>ชื่อผู้ใช้</strong> <?php echo htmlspecialchars($username); ?></p>
                <a href="logout.php" class="logout-button">ออกจากระบบ</a>
            </div>
        </div>
    </div>
                            
    <div class="menu">
    <!-- <a href="upload_form.php" class="menu-item">📤 อัปโหลดไฟล์ .xlsx</a> -->
    <a href="view_files.php" class="menu-item">📁 จัดการไฟล์</a>
    <a href="manage_user.php" class="menu-item">👥 จัดการผู้ใช้</a>
</div>


    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById("profileDropdown");
            dropdown.classList.toggle("show");
        }

        window.onclick = function(event) {
            if (!event.target.closest('.profile-container')) {
                const dropdown = document.getElementById("profileDropdown");
                dropdown.classList.remove('show');
            }
        }
    </script>

</body>
</html>
