<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=logowanie");
    exit;
}

$conn = new mysqli("localhost", "root", "", "systemzarzadzania");
$user_id = $_SESSION['user_id'];
$project_id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("
    SELECT p.name, p.owner_id, pm.role 
    FROM projects p
    LEFT JOIN project_members pm ON pm.project_id = p.id AND pm.user_id = ?
    WHERE p.id = ?
");
$stmt->bind_param("ii", $user_id, $project_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
    echo "<div class='tasks-page'><div class='task-card'>Projekt nie istnieje lub brak dostępu.</div></div>";
    exit;
}

$project = $res->fetch_assoc();
$is_owner = ($project['owner_id'] == $user_id);
$is_admin = (!empty($project['role']) && $project['role'] === 'admin');

if (!$is_owner && !$is_admin) {
    echo "<div class='tasks-page'><div class='task-card'>Brak dostępu do zarządzania członkami.</div></div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $member_id = $_POST['member_id'] ?? 0;
    
    if ($action === 'remove') {
        $remStmt = $conn->prepare("DELETE FROM project_members WHERE project_id = ? AND user_id = ?");
        $remStmt->bind_param("ii", $project_id, $member_id);
        $remStmt->execute();
        $remStmt->close();
    } elseif ($action === 'change_role') {
        $new_role = $_POST['role'] ?? 'viewer';
        $updStmt = $conn->prepare("UPDATE project_members SET role = ? WHERE project_id = ? AND user_id = ?");
        $updStmt->bind_param("sii", $new_role, $project_id, $member_id);
        $updStmt->execute();
        $updStmt->close();
    }
    
    header("Location: index.php?page=project_members&id=" . $project_id);
    exit;
}

$membersStmt = $conn->prepare("
    SELECT u.id, u.login, u.first_name, u.last_name, pm.role 
    FROM project_members pm
    JOIN users u ON pm.user_id = u.id
    WHERE pm.project_id = ?
    ORDER BY pm.role ASC, u.login ASC
");
$membersStmt->bind_param("i", $project_id);
$membersStmt->execute();
$members = $membersStmt->get_result();

function rolePL($role)
{
    return [
        'admin' => 'Administrator',
        'member' => 'Może edytować',
        'viewer' => 'Tylko odczyt'
    ][$role] ?? $role;
}
?>

<div class="tasks-page">
    <div class="tasks-header">
        <h2>Członkowie projektu: <?= htmlspecialchars($project['name']) ?></h2>
    </div>

    <a href="index.php?page=project_view&id=<?= $project_id ?>" class="btn btn-secondary" style="margin-bottom: 20px; display: inline-block; text-decoration: none;">
        &larr; Wróć do projektu
    </a>

    <div class="tasks-grid">
        <?php while ($m = $members->fetch_assoc()): ?>
            <div class="task-card">
                <div class="task-top">
                    <h3><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></h3>
                </div>
                <p class="desc">@<?= htmlspecialchars($m['login']) ?></p>
                <div class="task-meta" style="margin-bottom: 15px;">
                    <div><b>Obecna rola:</b> <?= rolePL($m['role']) ?></div>
                </div>

                <div style="border-top: 1px solid #eee; padding-top: 15px;">
                    <form method="POST" style="margin-bottom: 10px; display: flex; gap: 5px;">
                        <input type="hidden" name="action" value="change_role">
                        <input type="hidden" name="member_id" value="<?= $m['id'] ?>">
                        <select name="role" style="flex: 1; padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="admin" <?= $m['role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                            <option value="member" <?= $m['role'] === 'member' ? 'selected' : '' ?>>Może edytować</option>
                            <option value="viewer" <?= $m['role'] === 'viewer' ? 'selected' : '' ?>>Tylko odczyt</option>
                        </select>
                        <button type="submit" class="btn btn-primary" style="padding: 5px 10px;">Zmień</button>
                    </form>

                    <form method="POST" onsubmit="return confirm('Usunąć tego członka?');" style="margin: 0;">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="member_id" value="<?= $m['id'] ?>">
                        <button type="submit" class="btn btn-secondary" style="background-color: #dc3545; color: white; border-color: #dc3545; width: 100%;">
                            Usuń z projektu
                        </button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php
$stmt->close();
$membersStmt->close();
$conn->close();
?>
