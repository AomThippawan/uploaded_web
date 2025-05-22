<?php
include 'config_db.php';

// ดึงจำนวน goal ทั้งหมด
$total_sql = "SELECT COUNT(*) as total FROM goal";
$total_result = mysqli_query($conn, $total_sql);
$total = mysqli_fetch_assoc($total_result)['total'];

// ดึงจำนวน completed
$completed_sql = "SELECT COUNT(*) as completed FROM goal WHERE status = 'completed'";
$completed_result = mysqli_query($conn, $completed_sql);
$completed = mysqli_fetch_assoc($completed_result)['completed'];

// ดึงจำนวน pending
$pending_sql = "SELECT COUNT(*) as pending FROM goal WHERE status = 'pending'";
$pending_result = mysqli_query($conn, $pending_sql);
$pending = mysqli_fetch_assoc($pending_result)['pending'];

// ดึง goal ที่ใกล้ deadline (ภายใน 3 วันจากวันนี้)
$near_deadline_sql = "SELECT * FROM goal WHERE DATEDIFF(deadline, CURDATE()) <= 3 AND status != 'completed'";
$near_deadline_result = mysqli_query($conn, $near_deadline_sql);

// สำหรับ Bar Chart: นับจำนวน Completed และ Pending เปรียบเทียบตามวันที่ deadline
$bar_sql = "
    SELECT DATE(deadline) as goal_date,
           SUM(status = 'completed') as completed,
           SUM(status = 'pending') as pending
    FROM goal
    WHERE deadline IS NOT NULL
    GROUP BY DATE(deadline)
    ORDER BY goal_date ASC
";
$bar_result = mysqli_query($conn, $bar_sql);

$labels = [];
$completedData = [];
$pendingData = [];

while ($row = mysqli_fetch_assoc($bar_result)) {
    $labels[] = $row['goal_date'];
    $completedData[] = $row['completed'];
    $pendingData[] = $row['pending'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Goal Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">📊 Dashboard - ภาพรวมเป้าหมาย</h2>

    <div class="row text-center mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5>🎯 เป้าหมายทั้งหมด</h5>
                    <h2><?= $total ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5>✅ เสร็จแล้ว</h5>
                    <h2><?= $completed ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5>⌛ Pending</h5>
                    <h2><?= $pending ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5>⏰ ใกล้ถึง Deadline</h5>
                    <h2><?= mysqli_num_rows($near_deadline_result) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- รายการใกล้ deadline -->
    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            ⏰ รายการใกล้ถึง Deadline
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($near_deadline_result) > 0): ?>
                <ul class="list-group">
                    <?php while($row = mysqli_fetch_assoc($near_deadline_result)): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= $row['goal_name'] ?> (ถึงวันที่ <?= $row['deadline'] ?>)
                            <span class="badge bg-danger">เหลือ <?= (new DateTime())->diff(new DateTime($row['deadline']))->days ?> วัน</span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">ไม่มีรายการที่ใกล้ถึงกำหนดส่งใน 3 วัน</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bar Chart -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            📊 เปรียบเทียบเป้าหมาย Completed / Pending ตามวัน Deadline
        </div>
        <div class="card-body">
            <canvas id="goalChart" height="150"></canvas>
        </div>
    </div>

    <style>
    #goalChart {
        max-width: 100%;
    }
    </style>

    <script>
    const ctx = document.getElementById('goalChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [
                {
                    label: '✅ Completed',
                    data: <?= json_encode($completedData) ?>,
                    backgroundColor: '#198754'
                },
                {
                    label: '⌛ Pending',
                    data: <?= json_encode($pendingData) ?>,
                    backgroundColor: '#ffc107'
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'วันที่กำหนด (Deadline)'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'จำนวนเป้าหมาย (Goals)'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            }
        }
    });
    </script>

    <a href="admin.php" class="btn btn-outline-secondary">🔙 กลับไปหน้าแอดมิน</a>
</div>
</body>
</html>
