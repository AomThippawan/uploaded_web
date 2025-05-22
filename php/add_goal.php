<?php
session_start();
require('config_db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $goal_name = $_POST['goal_name'] ?? '';
    $description = $_POST['goal_description'] ?? '';
    $deadline = $_POST['goal_deadline'] ?? '';
    $status = $_POST['goal_status'] ?? 'pending';
    $file_id = $_POST['uploaded_file_id'] ?? null;

    if ($goal_name && $description && $deadline && $file_id) {
        $stmt = $conn->prepare("INSERT INTO goal (goal_name, description, deadline, status, uploaded_file_id, created_at, target_value) 
                                VALUES (?, ?, ?, ?, ?, NOW(), 0)");
        $stmt->bind_param("ssssi", $goal_name, $description, $deadline, $status, $file_id);
        $stmt->execute();
    }
}

header("Location: goal_details.php?file_id=" . urlencode($file_id));
exit;
