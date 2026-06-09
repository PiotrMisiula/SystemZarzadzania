<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=logowanie");
    exit;
}

$conn = new mysqli("localhost", "root", "", "systemzarzadzania");
$user_id = $_SESSION['user_id'];
$project_id = $_POST['project_id'] ?? 0;
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$visibility = $_POST['visibility'] ?? 'private';
$share_role = $_POST['share_role'] ?? 'viewer';

if ($name === '') {
    header("Location: index.php?page=projects");
    exit;
}

$stmt = $conn->prepare("
    SELECT p.owner_id, pm.role 
    FROM projects p 
    LEFT JOIN project_members pm ON pm.project_id = p.id AND pm.user_id = ? 
    WHERE p.id = ?
");
$stmt->bind_param("ii", $user_id, $project_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 1) {
    $row = $res->fetch_assoc();
    if ($row['owner_id'] == $user_id || $row['role'] === 'admin') {
        $updStmt = $conn->prepare("UPDATE projects SET name = ?, description = ?, visibility = ?, share_role = ? WHERE id = ?");
        $updStmt->bind_param("ssssi", $name, $description, $visibility, $share_role, $project_id);
        $updStmt->execute();
        $updStmt->close();
    }
}

$stmt->close();
$conn->close();

header("Location: index.php?page=projects");
exit;
?>
