<?php

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=logowanie");
    exit();
}

$conn = new mysqli("localhost", "root", "", "systemzarzadzania");
$user_id = $_SESSION['user_id'];

if (isset($_POST['edit_task'])) {
    $task_id = $_POST['task_id'];
    $project_id = !empty($_POST['project_id']) ? $_POST['project_id'] : null;
    $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;

    $updateStmt = $conn->prepare("
        UPDATE tasks
        SET project_id = ?, title = ?, description = ?, status = ?, priority = ?, deadline = ?, backgroundColor = ?, start_date = ?
        WHERE id = ? AND created_by = ?
    ");

    $updateStmt->bind_param(
        "isssssssii",
        $project_id,
        $_POST['title'],
        $_POST['description'],
        $_POST['status'],
        $_POST['priority'],
        $deadline,
        $_POST['backgroundColor'],
        $_POST['start_date'],
        $task_id,
        $user_id
    );

    $updateStmt->execute();
    header("Location: index.php?page=my_tasks");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = !empty($_POST['project_id']) ? $_POST['project_id'] : null;
    $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;

    $addStmt = $conn->prepare("
        INSERT INTO tasks 
        (project_id, created_by, title, description, status, priority, deadline, backgroundColor, start_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $addStmt->bind_param(
        "iisssssss",
        $project_id,
        $user_id,
        $_POST['title'],
        $_POST['description'],
        $_POST['status'],
        $_POST['priority'],
        $deadline,
        $_POST['backgroundColor'],
        $_POST['start_date']
    );

    $addStmt->execute();
    header("Location: index.php?page=my_tasks");
    exit();
}

$projectsStmt = $conn->prepare("
    SELECT DISTINCT p.id, p.name
    FROM projects p
    LEFT JOIN project_members pm ON pm.project_id = p.id
    WHERE p.owner_id = ? OR pm.user_id = ?
    ORDER BY p.name ASC
");
$projectsStmt->bind_param("ii", $user_id, $user_id);
$projectsStmt->execute();
$projectsResult = $projectsStmt->get_result();

$projects = [];
while ($p = $projectsResult->fetch_assoc()) {
    $projects[] = $p;
}

$stmt = $conn->prepare("
    SELECT 
        t.id, t.project_id, t.title, t.description, t.status, t.priority,
        t.deadline, t.created_at, t.backgroundColor, t.start_date,
        p.name AS project_name,
        u.first_name, u.last_name
    FROM tasks t
    JOIN users u ON t.created_by = u.id
    LEFT JOIN projects p ON t.project_id = p.id
    WHERE t.created_by = ?
    ORDER BY t.deadline ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

function statusPL($s) {
    return ['todo'=>'Do zrobienia', 'in_progress'=>'W trakcie', 'completed'=>'Zrobione'][$s] ?? $s;
}

function priorityPL($p) {
    return ['low'=>'Niski', 'medium'=>'Średni', 'high'=>'Wysoki'][$p] ?? $p;
}
?>

<div class="tasks-page">

    <div class="tasks-header">
        <h2>Moje zadania</h2>
        <div class="tasks-count"><?= $result->num_rows ?> zadań</div>
    </div>

    <button class="btn btn-primary" onclick='openTaskModal()' style="margin-bottom:25px;">
        Nowe zadanie
    </button>

    <div class="tasks-grid">
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="task-card" style="border-left: 6px solid <?= htmlspecialchars($row['backgroundColor'] ?? '#3b82f6') ?>">

                <div class="task-top">
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                </div>

                <p class="desc"><?= nl2br(htmlspecialchars($row['description'])) ?></p>

                <div class="task-meta">
                    <div><b>Projekt:</b> <?= htmlspecialchars($row['project_name'] ?? 'Bez projektu') ?></div>
                    <div><b>Autor:</b> <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></div>
                    <div><b>Początek zadania:</b> <?= date('d.m.Y H:i', strtotime($row['start_date'])) ?></div>
                    <div>
                        <b>Koniec zadania:</b>
                        <?= !empty($row['deadline']) ? date('d.m.Y H:i', strtotime($row['deadline'])) : 'Brak terminu' ?>
                    </div>
                </div>

                <div class="badges">
                    <span class="badge status"><?= statusPL($row['status']) ?></span>
                    <span class="badge priority"><?= priorityPL($row['priority']) ?></span>
                </div>

                <button
                    class="btn btn-primary"
                    type="button"
                    style="margin-top:12px;"
                    onclick='openTaskModal(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                >
                    Edytuj
                </button>

            </div>
        <?php endwhile; ?>
    </div>

</div>

<script>
const projects = <?= json_encode($projects, JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

function formatForInput(dateText) {
    if (!dateText) return "";
    const d = new Date(dateText.replace(" ", "T"));
    const pad = n => n.toString().padStart(2, "0");

    return d.getFullYear() + "-" +
        pad(d.getMonth() + 1) + "-" +
        pad(d.getDate()) + "T" +
        pad(d.getHours()) + ":" +
        pad(d.getMinutes());
}

function openTaskModal(task = null) {
    const modal = document.createElement("div");
    modal.className = "modal-overlay";

    let options = `<option value="">Bez projektu</option>`;

    projects.forEach(project => {
        const selected = task && String(task.project_id) === String(project.id) ? "selected" : "";
        options += `<option value="${project.id}" ${selected}>${project.name}</option>`;
    });

    modal.innerHTML = `
        <div class="modal">
            <h3>${task ? "Edytuj zadanie" : "Nowe zadanie"}</h3>

            <form method="POST">
                ${task ? `<input type="hidden" name="edit_task" value="1">
                         <input type="hidden" name="task_id" value="${task.id}">` : ""}

                <label>Projekt</label>
                <select name="project_id">${options}</select>

                <label>Nazwa</label>
                <input type="text" name="title" value="${task ? task.title : ""}" placeholder="Np. Spotkanie z klientem" required>

                <label>Opis</label>
                <textarea name="description" rows="3" placeholder="Szczegóły zadania...">${task ? task.description : ""}</textarea>

                <label>Data od</label>
                <input type="datetime-local" name="start_date" value="${task ? formatForInput(task.start_date) : ""}">

                <label>Data do</label>
                <input type="datetime-local" name="deadline" value="${task ? formatForInput(task.deadline) : ""}">

                <label>Status</label>
                <select name="status">
                    <option value="todo" ${task && task.status === "todo" ? "selected" : ""}>Do zrobienia</option>
                    <option value="in_progress" ${task && task.status === "in_progress" ? "selected" : ""}>W trakcie</option>
                    <option value="completed" ${task && task.status === "completed" ? "selected" : ""}>Zrobione</option>
                </select>

                <label>Priorytet</label>
                <select name="priority">
                    <option value="low" ${task && task.priority === "low" ? "selected" : ""}>Niski</option>
                    <option value="medium" ${task && task.priority === "medium" ? "selected" : ""}>Średni</option>
                    <option value="high" ${task && task.priority === "high" ? "selected" : ""}>Wysoki</option>
                </select>

                <label>Kolor</label>
                <input type="color" name="backgroundColor" value="${task && task.backgroundColor ? task.backgroundColor : "#3b82f6"}">

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="closeModal">Anuluj</button>
                    <button type="submit" class="btn btn-primary">Zapisz</button>
                </div>
            </form>
        </div>
    `;

    document.body.appendChild(modal);

    modal.querySelector("#closeModal").onclick = () => modal.remove();
}
</script>

<?php
$stmt->close();
$projectsStmt->close();
$conn->close();
?>