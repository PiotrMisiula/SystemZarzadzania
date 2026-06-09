<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=logowanie");
    exit;
}

$conn = new mysqli("localhost", "root", "", "systemzarzadzania");
$user_id = $_SESSION['user_id'];
$project_id = $_POST['project_id'] ?? 0;

$stmt = $conn->prepare("SELECT owner_id FROM projects WHERE id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 1) {
    $row = $res->fetch_assoc();
    if ($row['owner_id'] == $user_id) {
        $delTasksStmt = $conn->prepare("DELETE FROM tasks WHERE project_id = ?");
        $delTasksStmt->bind_param("i", $project_id);
        $delTasksStmt->execute();
        $delTasksStmt->close();

        $delProjStmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
        $delProjStmt->bind_param("i", $project_id);
        $delProjStmt->execute();
        $delProjStmt->close();
    }
}

$stmt->close();
$conn->close();

header("Location: index.php?page=projects");
exit;
?>
