<?php
session_start();

$conn = new mysqli("localhost", "root", "", "systemzarzadzania");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}

$name = trim($_POST['name'] ?? '');
$desc = trim($_POST['description'] ?? '');
$visibility = $_POST['visibility'] ?? 'private';

if ($name === '') {
    header("Location: index.php?page=projects");
    exit;
}

if ($visibility !== 'public' && $visibility !== 'private') {
    $visibility = 'private';
}

$key = null;

if ($visibility === 'private') {
    $key = bin2hex(random_bytes(16));
}

$stmt = $conn->prepare("
    INSERT INTO projects (owner_id, name, description, visibility, access_key)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "issss",
    $_SESSION['user_id'],
    $name,
    $desc,
    $visibility,
    $key
);

$stmt->execute();

$project_id = $conn->insert_id;

$stmtMember = $conn->prepare("
    INSERT INTO project_members (project_id, user_id, role)
    VALUES (?, ?, 'admin')
");

$stmtMember->bind_param("ii", $project_id, $_SESSION['user_id']);
$stmtMember->execute();

$stmt->close();
$stmtMember->close();
$conn->close();

header("Location: index.php?page=project_view&id=" . $project_id);
exit;
