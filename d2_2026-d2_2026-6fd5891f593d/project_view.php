<?php
$conn = new mysqli("localhost", "root", "", "systemzarzadzania");

$user_id = $_SESSION['user_id'];
$project_id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmtAdd = $conn->prepare("
        INSERT INTO tasks 
        (project_id, created_by, title, description, status, priority, deadline, backgroundColor)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $deadline = $_POST['deadline'] ?: null;

    $stmtAdd->bind_param(
        "iissssss",
        $project_id,
        $user_id,
        $_POST['title'],
        $_POST['description'],
        $_POST['status'],
        $_POST['priority'],
        $deadline,
        $_POST['backgroundColor']
    );

    $stmtAdd->execute();

    header("Location: index.php?page=project_view&id=" . $project_id);
    exit();
}

$stmt = $conn->prepare("SELECT name, description FROM projects WHERE id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

if (!$project) {
    echo "<div class='tasks-page'><div class='task-card'>Projekt nie istnieje.</div></div>";
    exit;
}

$tasksStmt = $conn->prepare("
    SELECT title, description, status, priority, deadline, backgroundColor
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

    <button class="btn btn-primary" onclick="openTaskModal()" style="margin-bottom:25px;">
        Nowe zadanie
    </button>

    <div id="taskModal" class="modal-overlay" style="display:none;">
        <div class="modal">
            <h3>Nowe zadanie</h3>

            <form method="POST">
                <label>Nazwa</label>
                <input type="text" name="title" placeholder="Np. Spotkanie z klientem" required>

                <label>Opis</label>
                <textarea name="description" rows="3" placeholder="Szczegóły zadania..."></textarea>

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