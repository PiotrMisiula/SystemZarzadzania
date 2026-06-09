<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=logowanie");
    exit;
}

$conn = new mysqli("localhost", "root", "", "systemzarzadzania");

$access_key = trim($_POST['access_key'] ?? '');

if (empty($access_key)) {
    header("Location: index.php?page=projects");
    exit;
}

$stmt = $conn->prepare("SELECT id, share_role FROM projects WHERE access_key = ? AND visibility = 'private'");
$stmt->bind_param("s", $access_key);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 1) {
    $row = $res->fetch_assoc();
    $project_id = $row['id'];
    $user_id = $_SESSION['user_id'];
    $role = $row['share_role'] ? $row['share_role'] : 'viewer';

    $checkStmt = $conn->prepare("SELECT role FROM project_members WHERE project_id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $project_id, $user_id);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows === 0) {
        $addStmt = $conn->prepare("INSERT INTO project_members (project_id, user_id, role) VALUES (?, ?, ?)");
        $addStmt->bind_param("iis", $project_id, $user_id, $role);
        $addStmt->execute();
        $addStmt->close();
    }
    $checkStmt->close();

    header("Location: index.php?page=project_view&id=" . $project_id);
} else {
    header("Location: index.php?page=projects&error=invalid_key");
}

$stmt->close();
$conn->close();
exit;
