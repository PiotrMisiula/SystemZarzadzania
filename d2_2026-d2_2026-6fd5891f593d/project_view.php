<?php
$conn = new mysqli("localhost", "root", "", "systemzarzadzania");

$user_id = $_SESSION['user_id'] ?? 0;
$project_id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("
    SELECT 
        p.name, p.description, p.visibility, p.owner_id, p.share_role, 
        pm.role 
    FROM projects p
    LEFT JOIN project_members pm ON pm.project_id = p.id AND pm.user_id = ?
    WHERE p.id = ?
");
$stmt->bind_param("ii", $user_id, $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

if (!$project) {
    echo "<div class='tasks-page'><div class='task-card'>Projekt nie istnieje.</div></div>";
    exit;
}

$is_owner = ($project['owner_id'] == $user_id);
$is_member = !empty($project['role']);
$is_public = ($project['visibility'] === 'public');

if (!$is_owner && !$is_member && !$is_public) {
    echo "<div class='tasks-page'><div class='task-card'>Brak dostępu do projektu.</div></div>";
    exit;
}

$can_edit = false;
if ($is_owner) {
    $can_edit = true;
} elseif ($is_member && in_array($project['role'], ['admin', 'member'])) {
    $can_edit = true;
} elseif (!$is_member && $is_public && $project['share_role'] === 'member') {
    $can_edit = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $can_edit) {
    $stmtAdd = $conn->prepare("
        INSERT INTO tasks 
        (project_id, created_by, title, description, status, priority, deadline, backgroundColor, start_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d H:i:s');

    $stmtAdd->bind_param(
        "iisssssss",
        $project_id,
        $user_id,
        $_POST['title'],
        $_POST['description'],
        $_POST['status'],
        $_POST['priority'],
        $deadline,
        $_POST['backgroundColor'],
        $start_date
    );

    $stmtAdd->execute();

    header("Location: index.php?page=project_view&id=" . $project_id);
    exit();
}

$tasksStmt = $conn->prepare("
    SELECT id, title, description, status, priority, deadline, backgroundColor, created_by
    FROM tasks
    WHERE project_id = ?
    ORDER BY created_at DESC
");
$tasksStmt->bind_param("i", $project_id);
$tasksStmt->execute();
$tasks = $tasksStmt->get_result();

function statusPL($s)
{
    return ['todo' => 'Do zrobienia', 'in_progress' => 'W trakcie', 'completed' => 'Zrobione'][$s] ?? $s;
}

function priorityPL($p)
{
    return ['low' => 'Niski', 'medium' => 'Średni', 'high' => 'Wysoki'][$p] ?? $p;
}
?>

<div class="tasks-page">

    <div class="tasks-header">
        <h2><?= htmlspecialchars($project['name']) ?></h2>
        <div class="tasks-count"><?= $tasks->num_rows ?> zadań</div>
    </div>

    <p class="desc" style="margin-bottom:20px;">
        <?= nl2br(htmlspecialchars($project['description'])) ?>
    </p>

    <div style="display: flex; gap: 10px; margin-bottom: 25px;">
        <?php if ($can_edit): ?>
            <button class="btn btn-primary" onclick="openTaskModal()">
                Nowe zadanie
            </button>
        <?php endif; ?>
        
        <?php if ($is_owner || (!empty($project['role']) && $project['role'] === 'admin')): ?>
            <a href="index.php?page=project_members&id=<?= $project_id ?>" class="btn btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                Zarządzaj członkami
            </a>
        <?php endif; ?>
    </div>

    <div id="taskModal" class="modal-overlay" style="display:none;">
        <div class="modal">
            <h3>Nowe zadanie</h3>

            <form method="POST">
                <label>Nazwa</label>
                <input type="text" name="title" placeholder="Np. Spotkanie z klientem" required>

                <label>Opis</label>
                <textarea name="description" rows="3" placeholder="Szczegóły zadania..."></textarea>

                <label>Data od</label>
                <input type="datetime-local" name="start_date" value="<?= date('Y-m-d\TH:i') ?>">

                <label>Data do</label>
                <input type="datetime-local" name="deadline">

                <label>Status</label>
                <select name="status">
                    <option value="todo">Do zrobienia</option>
                    <option value="in_progress">W trakcie</option>
                    <option value="completed">Zrobione</option>
                </select>

                <label>Priorytet</label>
                <select name="priority">
                    <option value="low">Niski</option>
                    <option value="medium">Średni</option>
                    <option value="high">Wysoki</option>
                </select>

                <label>Kolor</label>
                <input type="color" name="backgroundColor" value="#3b82f6">

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeTaskModal()">Anuluj</button>
                    <button type="submit" class="btn btn-primary">Zapisz</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($tasks->num_rows == 0): ?>
        <div class="task-card">Ten projekt nie ma jeszcze zadań.</div>
    <?php endif; ?>

    <div class="tasks-grid">
        <?php while ($t = $tasks->fetch_assoc()): ?>
            <div class="task-card" style="border-left: 6px solid <?= htmlspecialchars($t['backgroundColor'] ?? '#3b82f6') ?>">
                <div class="task-top">
                    <h3><?= htmlspecialchars($t['title']) ?></h3>
                </div>

                <p class="desc"><?= nl2br(htmlspecialchars($t['description'])) ?></p>

                <?php if (!empty($t['deadline'])): ?>
                    <div style="margin-bottom:10px;">
                        <b>Termin:</b> <?= date('d.m.Y H:i', strtotime($t['deadline'])) ?>
                    </div>
                <?php endif; ?>

                <div class="badges">
                    <span class="badge"><?= statusPL($t['status']) ?></span>
                    <span class="badge"><?= priorityPL($t['priority']) ?></span>
                </div>

                <?php 
                $can_delete_task = false;
                if ($t['created_by'] == $user_id) $can_delete_task = true;
                elseif ($is_owner || (!empty($project['role']) && $project['role'] === 'admin')) $can_delete_task = true;
                ?>

                <?php if ($can_edit || $can_delete_task): ?>
                <div style="display: flex; gap: 5px; margin-top: 15px;">
                    <?php if ($can_edit && $t['status'] !== 'completed'): ?>
                    <form action="complete_task.php" method="POST" style="margin: 0; flex: 1; display: flex;">
                        <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
                        <input type="hidden" name="project_id" value="<?= $project_id ?>">
                        <input type="hidden" name="return_page" value="project_view">
                        <input type="hidden" name="status" value="completed">
                        <button class="btn btn-primary" type="submit" style="width: 100%; background-color: #28a745;">Zakończ</button>
                    </form>
                    <?php endif; ?>
                    
                    <?php if ($can_delete_task): ?>
                    <form action="delete_task.php" method="POST" onsubmit="return confirm('Czy na pewno chcesz usunąć to zadanie?');" style="margin: 0; flex: 1; display: flex;">
                        <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
                        <input type="hidden" name="project_id" value="<?= $project_id ?>">
                        <input type="hidden" name="return_page" value="project_view">
                        <button class="btn btn-secondary" type="submit" style="background-color: #dc3545; color: white; width: 100%;">Usuń</button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

</div>

<script>
    function openTaskModal() {
        document.getElementById("taskModal").style.display = "flex";
    }

    function closeTaskModal() {
        document.getElementById("taskModal").style.display = "none";
    }
</script>

<?php
$stmt->close();
$tasksStmt->close();
$conn->close();
?>