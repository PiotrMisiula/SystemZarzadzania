<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=logowanie");
    exit;
}

$conn = new mysqli("localhost", "root", "", "systemzarzadzania");
$user_id = $_SESSION['user_id'];
$task_id = $_POST['task_id'] ?? 0;
$return_page = $_POST['return_page'] ?? 'my_tasks';
$project_id = $_POST['project_id'] ?? '';

$stmt = $conn->prepare("
    SELECT t.created_by, t.project_id, p.owner_id, pm.role 
    FROM tasks t
    LEFT JOIN projects p ON t.project_id = p.id
    LEFT JOIN project_members pm ON pm.project_id = p.id AND pm.user_id = ?
    WHERE t.id = ?
");
$stmt->bind_param("ii", $user_id, $task_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 1) {
    $row = $res->fetch_assoc();
    $can_delete = false;
    if ($row['created_by'] == $user_id) {
        $can_delete = true;
    } elseif (!empty($row['project_id'])) {
        if ($row['owner_id'] == $user_id || $row['role'] === 'admin') {
            $can_delete = true;
        }
    }
    
    if ($can_delete) {
        $delStmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
        $delStmt->bind_param("i", $task_id);
        $delStmt->execute();
        $delStmt->close();
    }
}

$stmt->close();
$conn->close();

if ($return_page === 'project_view' && !empty($project_id)) {
    header("Location: index.php?page=project_view&id=" . $project_id);
} else {
    header("Location: index.php?page=my_tasks");
}
exit;
?>
