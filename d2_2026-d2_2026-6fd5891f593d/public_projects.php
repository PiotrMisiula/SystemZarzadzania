<?php
$conn = new mysqli("localhost", "root", "", "systemzarzadzania");

$user_id = $_SESSION['user_id'] ?? 0;

$stmt = $conn->prepare("
    SELECT 
        p.id,
        p.name,
        p.description,
        p.visibility,
        p.share_role,
        p.created_at,
        u.login as owner_name,
        COUNT(t.id) AS task_count
    FROM projects p
    LEFT JOIN tasks t ON t.project_id = p.id
    LEFT JOIN users u ON p.owner_id = u.id
    WHERE p.visibility = 'public'
    GROUP BY p.id, u.login
    ORDER BY p.created_at DESC
");

$stmt->execute();
$result = $stmt->get_result();

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
        <h2>Publiczne projekty</h2>
        <div class="tasks-count">
            <?= $result->num_rows ?> projektów
        </div>
    </div>

    <div class="tasks-grid">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="task-card">

                <div class="task-top">
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                </div>

                <p class="desc">
                    <?= nl2br(htmlspecialchars($row['description'])) ?>
                </p>

                <div class="task-meta">
                    <div><b>Właściciel:</b> <?= htmlspecialchars($row['owner_name']) ?></div>
                    <div><b>Utworzono:</b> <?= date('d.m.Y H:i', strtotime($row['created_at'])) ?></div>
                    <div><b>Zadań w projekcie:</b> <?= $row['task_count'] ?></div>
                    <div><b>Dostęp:</b> <?= rolePL($row['share_role']) ?></div>
                </div>

                <div class="badges" style="margin-top:10px;">
                    <span class="badge">
                        Publiczny
                    </span>
                </div>

                <form action="index.php" method="GET" style="margin-top:12px;">
                    <input type="hidden" name="page" value="project_view">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button class="btn btn-primary" type="submit">
                        Otwórz projekt
                    </button>
                </form>

            </div>
        <?php endwhile; ?>
    </div>

</div>

<?php
$stmt->close();
$conn->close();
?>
