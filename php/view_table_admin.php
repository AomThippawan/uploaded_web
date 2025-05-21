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

if ($role !== 'admin' && !in_array($username, $allowed_users)) {
    die("คุณไม่มีสิทธิ์ดูข้อมูลตารางนี้");
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
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
        }
        th {
            background-color: #eee;
        }
        td[contenteditable="true"] {
            background-color:rgb(255, 255, 255);
        }
    </style>
</head>
<body>

<h2>ข้อมูลในไฟล์: <?php echo htmlspecialchars($table); ?></h2>
<a href="<?php echo ($role === 'admin') ? 'admin.php' : 'user.php'; ?>">🔙 ย้อนกลับ</a>

<div class="table-container">
    <table>
        <?php if ($result->num_rows > 0): ?>
            <thead>
                <tr>
                    <?php
                    $firstRow = $result->fetch_assoc();
                    $columns = array_keys($firstRow);
                    // กรองไม่เอาคอลัมน์ 'id'
                    $columns_no_id = array_filter($columns, fn($col) => $col !== 'id');
                    foreach ($columns_no_id as $col) {
                        echo "<th>" . htmlspecialchars($col) . "</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // แสดงแถวแรก โดยไม่แสดงคอลัมน์ id
                echo "<tr data-id='{$firstRow['id']}'>";
                foreach ($columns_no_id as $col) {
                    echo "<td contenteditable='true' data-id='{$firstRow['id']}' data-column='" . htmlspecialchars($col) . "' data-table='" . htmlspecialchars($table) . "'>" . htmlspecialchars($firstRow[$col]) . "</td>";
                }
                echo "</tr>";

                // แสดงแถวที่เหลือ โดยไม่แสดงคอลัมน์ id
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
            <tr><td colspan="100%">ไม่มีข้อมูลในตาราง</td></tr>
        <?php endif; ?>
    </table>
</div>

<script>
document.querySelectorAll('td[contenteditable="true"]').forEach(cell => {
    cell.addEventListener('blur', () => {
        const id = cell.dataset.id;
        const column = cell.dataset.column;
        const table = cell.dataset.table;
        const value = cell.innerText.trim();

        fetch('update_cell.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
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
