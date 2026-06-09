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
$new_status = $_POST['status'] ?? 'completed';

$stmt = $conn->prepare("
    SELECT t.created_by, t.project_id, p.owner_id, p.share_role, p.visibility, pm.role 
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
    $can_edit = false;
    
    if ($row['created_by'] == $user_id) {
        $can_edit = true;
    } elseif (!empty($row['project_id'])) {
        if ($row['owner_id'] == $user_id) {
            $can_edit = true;
        } elseif (!empty($row['role']) && in_array($row['role'], ['admin', 'member'])) {
            $can_edit = true;
        } elseif ($row['visibility'] === 'public' && $row['share_role'] === 'member') {
            $can_edit = true;
        }
    }
    
    if ($can_edit) {
        $updStmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        $updStmt->bind_param("si", $new_status, $task_id);
        $updStmt->execute();
        $updStmt->close();
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
