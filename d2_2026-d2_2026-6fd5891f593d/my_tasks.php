<?php

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php?page=logowanie");
  exit();
}

$conn = new mysqli("localhost", "root", "", "systemzarzadzania");

$stmt = $conn->prepare("
    SELECT 
        t.title,
        t.description,
        t.status,
        t.priority,
        t.deadline,
        t.created_at,
        t.backgroundColor,
        u.first_name,
        u.last_name
    FROM tasks t
    JOIN users u ON t.created_by = u.id
    WHERE t.created_by = ?
    ORDER BY t.deadline ASC
");

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

function statusPL($s)
{
  return [
    'todo' => 'Do zrobienia',
    'in_progress' => 'W trakcie',
    'completed' => 'Zrobione'
  ][$s] ?? $s;
}

function priorityPL($p)
{
  return [
    'low' => 'Niski',
    'medium' => 'Średni',
    'high' => 'Wysoki'
  ][$p] ?? $p;
}
?>

<div class="tasks-page">

  <div class="tasks-header">
    <h2>Moje zadania</h2>
    <div class="tasks-count">
      Liczba zadań: <?= $result->num_rows ?>
    </div>
  </div>

  <div class="tasks-grid">
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="task-card" style="border-left: 6px solid <?= htmlspecialchars($row['backgroundColor']) ?>">

        <div class="task-top">
          <h3><?= htmlspecialchars($row['title']) ?></h3>
        </div>

        <p class="desc">
          <?= nl2br(htmlspecialchars($row['description'])) ?>
        </p>

        <div class="task-meta">
          <div><b>Autor:</b> <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></div>
          <div><b>Utworzono:</b> <?= date('d.m.Y H:i', strtotime($row['created_at'])) ?></div>

          <div>
            <b>Termin:</b>
            <?php if (!empty($row['deadline'])): ?>
              <?= date('d.m.Y H:i', strtotime($row['deadline'])) ?>
            <?php else: ?>
              Brak terminu
            <?php endif; ?>
          </div>
        </div>

        <div class="badges">
          <span class="badge status-<?= htmlspecialchars($row['status']) ?>"><?= statusPL($row['status']) ?></span>
          <span class="badge priority-<?= htmlspecialchars($row['priority']) ?>"><?= priorityPL($row['priority']) ?></span>
        </div>

      </div>
    <?php endwhile; ?>
  </div>

</div>

<?php
$stmt->close();
$conn->close();
?>