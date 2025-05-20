<?php
session_start();
include 'config_db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$table = isset($_GET['table']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['table']) : '';
if (!$table) die("ไม่พบชื่อตาราง");

$check = $conn->query("SHOW TABLES LIKE '$table'");
if ($check->num_rows === 0) die("ไม่พบตาราง $table");

$result = $conn->query("SELECT * FROM `$table`");
if (!$result) die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $conn->error);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ดูและจัดการตาราง: <?= htmlspecialchars($table) ?></title>
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
            background-color: #fff;
        }
        .actions {
            margin-top: 20px;
        }
        button {
            margin-right: 10px;
        }
    </style>
</head>
<body>

<h2>ตาราง: <?= htmlspecialchars($table) ?></h2>
<a href="view_files.php">🔙 ย้อนกลับ</a>

<div class="actions">
    <button onclick="addRow()">➕ เพิ่มแถว</button>
    <button onclick="deleteRow()">➖ ลบแถวสุดท้าย</button>
    <button onclick="addColumn()">➕ เพิ่มคอลัมน์</button>
    <button onclick="deleteColumn()">➖ ลบคอลัมน์สุดท้าย</button>
</div>

<table id="data-table">
    <thead>
        <tr>
            <?php
            $firstRow = $result->fetch_assoc();
            $columns = array_keys($firstRow);
            foreach ($columns as $col) {
                echo "<th>" . htmlspecialchars($col) . "</th>";
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <?php
        echo "<tr>";
        foreach ($columns as $col) {
            echo "<td contenteditable='true' data-column='" . htmlspecialchars($col) . "' data-id='" . $firstRow['id'] . "'>" . htmlspecialchars($firstRow[$col]) . "</td>";
        }
        echo "</tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($columns as $col) {
                echo "<td contenteditable='true' data-column='" . htmlspecialchars($col) . "' data-id='" . $row['id'] . "'>" . htmlspecialchars($row[$col]) . "</td>";
            }
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

<script>
const table = "<?= $table ?>";

// บันทึกเมื่อ blur
document.querySelectorAll('td[contenteditable="true"]').forEach(cell => {
    cell.addEventListener('blur', () => {
        const id = cell.dataset.id;
        const column = cell.dataset.column;
        const value = cell.innerText.trim();

        fetch('update_cell.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&column=${column}&value=${encodeURIComponent(value)}&table=${table}`
        }).then(r => r.text()).then(text => {
            if (text !== 'success') alert('❌ ' + text);
        });
    });
});

// เพิ่มแถวใหม่
function addRow() {
    fetch('add_row.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `table=${table}`
    }).then(() => location.reload());
}

// ลบแถวสุดท้าย
function deleteRow() {
    fetch('delete_row.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `table=${table}`
    }).then(() => location.reload());
}

// เพิ่มคอลัมน์
function addColumn() {
    const newCol = prompt("ชื่อคอลัมน์ใหม่:");
    if (!newCol) return;
    fetch('add_column.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `table=${table}&column=${encodeURIComponent(newCol)}`
    }).then(() => location.reload());
}

// ลบคอลัมน์สุดท้าย
function deleteColumn() {
    fetch('delete_column.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `table=${table}`
    }).then(() => location.reload());
}
</script>

</body>
</html>
