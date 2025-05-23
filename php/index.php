<?php
session_start();
include 'config_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ตรวจสอบข้อมูลผู้ใช้จากฐานข้อมูล
    $stmt = $conn->prepare("SELECT * FROM user_his WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        if ($_SESSION['role'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: user.php");
        }
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ</title>
    <!-- FontAwesome (ไอคอน) -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>
    <!-- TopBar-->
    <div class="topbar">
        <div class="topbar-right">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-tiktok"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
    </div>
    <!-- end TopBar-->
    <!-- LOGO BAR -->
    <div class="logobar">
        <div class="logo-text">
            <span class="rsa">RSA</span><span class="thai">THAI</span>
        </div>
    </div>
    <!-- end LOGO BAR -->
    <!-- LOGIN CARD -->
    <div class="container">
        <div class="login-card">
            <h2>เข้าสู่ระบบ</h2>
            <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
            <form action="index.php" method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="submit" value="เข้าสู่ระบบ">
            </form>
        </div>
    </div>

</body>

</html>