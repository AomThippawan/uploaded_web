<?php
session_start();
include 'config_db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

$table = isset($_GET['table']) ? preg_replace('/[^\p{L}\p{M}\p{N}_]/u', '', $_GET['table']) : '';

if (!$table) die("ไม่พบชื่อตาราง");

$check = $conn->query("SHOW TABLES LIKE '$table'");
if ($check->num_rows === 0) die("ไม่พบตาราง $table");

$stmt = $conn->prepare("SELECT allowed_users FROM uploaded_file WHERE REPLACE(filename, '.xlsx', '') = ?");
$stmt->bind_param("s", $table);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) die("ไม่พบข้อมูลไฟล์ในระบบ");

$row = $res->fetch_assoc();
$allowed_users = array_map('trim', explode(',', $row['allowed_users']));

// ดึงรายชื่อผู้ใช้ทั้งหมด (เฉพาะ role 'user')
$users_res = $conn->query("SELECT username FROM user_his WHERE role = 'user'");
$all_users = [];
while ($u = $users_res->fetch_assoc()) {
    $all_users[] = $u['username'];
}

if ($role !== 'admin' && !in_array($username, $allowed_users)) {
    die("คุณไม่มีสิทธิ์ดูข้อมูลตารางนี้");
}

// เมื่อ admin ส่งฟอร์มอัปเดต allowed_users
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'admin' && isset($_POST['allowed_users'])) {
    $new_allowed_users = implode(',', array_map('trim', $_POST['allowed_users']));

    $stmtUpdate = $conn->prepare("UPDATE uploaded_file SET allowed_users = ? WHERE REPLACE(filename, '.xlsx', '') = ?");
    $stmtUpdate->bind_param("ss", $new_allowed_users, $table);

    if ($stmtUpdate->execute()) {
        $allowed_users = array_map('trim', explode(',', $new_allowed_users)); // อัปเดตค่าที่ใช้แสดงในหน้า
        $success_message = "✅ อัปเดตสิทธิ์สำเร็จแล้ว";
    } else {
        $error_message = "❌ อัปเดตสิทธิ์ล้มเหลว: " . $stmtUpdate->error;
    }
}

// ดึงข้อมูลจากตาราง
$result = $conn->query("SELECT * FROM `$table`");
if (!$result) die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $conn->error);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ดูข้อมูลไฟล์: <?php echo htmlspecialchars($table); ?></title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        th {
            background-color: #eee;
        }

        td[contenteditable="true"] {
            background-color: rgb(255, 255, 255);
        }
        a {
            text-decoration-line: none;
        }
    </style>
</head>

<body>
    <h2>ไฟล์: <?php echo htmlspecialchars($table); ?></h2>
    <?php if ($role === 'admin'): ?>
        <?php if (isset($success_message)) echo "<script>alert('$success_message');</script>"; ?>
        <?php if (isset($error_message)) echo "<p style='color: red;'>$error_message</p>"; ?>


        <button id="toggle-user-form" style="float: right; padding: 6px 12px; margin-bottom: 10px;">➕ สิทธิ์การเข้าถึง</button>

        <form method="post" id="user-form" style="display:none; margin-top: 10px;">
            <div style="border: 1px solid #ccc; padding: 10px; max-height: 200px; overflow-y: auto;">
                <?php foreach ($all_users as $user): ?>
                    <label style="display:block; margin-bottom: 5px;">
                        <input type="checkbox" name="allowed_users[]" value="<?= htmlspecialchars($user) ?>"
                            <?= in_array($user, $allowed_users) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($user) ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <br>
            <button type="submit" style="padding: 6px 12px;">💾</button>
        </form>
    <?php endif; ?>

    
    <a href="<?php echo ($role === 'admin') ? 'view_files.php' : 'user.php'; ?>">🔙 ย้อนกลับ</a>

    <div class="table-container">
        <table>
            <?php if ($result->num_rows > 0): ?>
                <thead>
                    <tr>
                        <?php
                        $firstRow = $result->fetch_assoc();
                        $columns = array_keys($firstRow);
                        $columns_no_id = array_filter($columns, fn($col) => $col !== 'id');
                        foreach ($columns_no_id as $col) {
                            echo "<th>" . htmlspecialchars($col) . "</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    echo "<tr data-id='{$firstRow['id']}'>";
                    foreach ($columns_no_id as $col) {
                        echo "<td contenteditable='true' data-id='{$firstRow['id']}' data-column='" . htmlspecialchars($col) . "' data-table='" . htmlspecialchars($table) . "'>" . htmlspecialchars($firstRow[$col]) . "</td>";
                    }
                    echo "</tr>";

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr data-id='{$row['id']}'>";
                        foreach ($columns_no_id as $col) {
                            echo "<td contenteditable='true' data-id='{$row['id']}' data-column='" . htmlspecialchars($col) . "' data-table='" . htmlspecialchars($table) . "'>" . htmlspecialchars($row[$col]) . "</td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            <?php else: ?>
                <tr>
                    <td colspan="100%">ไม่มีข้อมูลในตาราง</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <script>
        document.getElementById("toggle-user-form").addEventListener("click", () => {
            const form = document.getElementById("user-form");
            form.style.display = (form.style.display === "none") ? "block" : "none";
        });

        document.querySelectorAll('td[contenteditable="true"]').forEach(cell => {
            cell.addEventListener('blur', () => {
                const id = cell.dataset.id;
                const column = cell.dataset.column;
                const table = cell.dataset.table;
                const value = cell.innerText.trim();

                fetch('update_cell.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id=${id}&column=${encodeURIComponent(column)}&value=${encodeURIComponent(value)}&table=${encodeURIComponent(table)}`
                    })
                    .then(res => res.text())
                    .then(response => {
                        if (response !== 'success') {
                            alert('❌ แก้ไขข้อมูลไม่สำเร็จ: ' + response);
                        }
                    })
                    .catch(err => alert('❌ ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์'));
            });
        });
    </script>

</body>

</html>