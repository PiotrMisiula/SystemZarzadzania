<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=logowanie");
    exit();
}

$userId = $_SESSION['user_id'];

try {
    $dbh = new PDO("mysql:host=localhost;dbname=systemzarzadzania", "root", "");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Stats
    $stmt = $dbh->prepare("SELECT COUNT(DISTINCT t.id) as total FROM tasks t LEFT JOIN task_assignments ta ON t.id = ta.task_id WHERE t.created_by = :uid OR ta.user_id = :uid");
    $stmt->execute(['uid' => $userId]);
    $totalTasks = $stmt->fetch()['total'] ?? 0;

    $stmt = $dbh->prepare("SELECT COUNT(DISTINCT t.id) as total FROM tasks t LEFT JOIN task_assignments ta ON t.id = ta.task_id WHERE (t.created_by = :uid OR ta.user_id = :uid) AND t.status = 'completed'");
    $stmt->execute(['uid' => $userId]);
    $completedTasks = $stmt->fetch()['total'] ?? 0;

    $stmt = $dbh->prepare("SELECT COUNT(DISTINCT p.id) as total FROM projects p LEFT JOIN project_members pm ON p.id = pm.project_id WHERE p.owner_id = :uid OR pm.user_id = :uid");
    $stmt->execute(['uid' => $userId]);
    $totalProjects = $stmt->fetch()['total'] ?? 0;

    // 2. Upcoming deadlines (next 5 tasks)
    $stmt = $dbh->prepare("
        SELECT DISTINCT t.*, p.name as project_name 
        FROM tasks t 
        LEFT JOIN task_assignments ta ON t.id = ta.task_id 
        LEFT JOIN projects p ON t.project_id = p.id
        WHERE (t.created_by = :uid OR ta.user_id = :uid) 
          AND t.status != 'completed' 
          AND t.deadline >= NOW()
        ORDER BY t.deadline ASC 
        LIMIT 4
    ");
    $stmt->execute(['uid' => $userId]);
    $upcomingTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Recent projects
    $stmt = $dbh->prepare("
        SELECT DISTINCT p.* 
        FROM projects p 
        LEFT JOIN project_members pm ON p.id = pm.project_id 
        WHERE p.owner_id = :uid OR pm.user_id = :uid
        ORDER BY p.created_at DESC 
        LIMIT 3
    ");
    $stmt->execute(['uid' => $userId]);
    $recentProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Błąd bazy danych.";
    exit();
}

function statusPL_dashboard($s) {
    return ['todo' => 'Do zrobienia', 'in_progress' => 'W trakcie', 'completed' => 'Zrobione'][$s] ?? $s;
}
function priorityPL_dashboard($p) {
    return ['low' => 'Niski', 'medium' => 'Średni', 'high' => 'Wysoki'][$p] ?? $p;
}
?>

<style>
.dashboard-header {
    margin-bottom: 30px;
    animation: fadeIn 0.4s ease;
}
.dashboard-header h2 {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 10px 0;
    font-family: "Playwrite DE SAS", cursive;
}
.dashboard-header p {
    color: #6b7280;
    font-size: 16px;
    margin: 0;
}
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
    animation: fadeIn 0.5s ease;
}
.stat-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    border: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}
.stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 100%; height: 4px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
}
.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    background: rgba(59, 130, 246, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #3b82f6;
}
.stat-icon.green { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.stat-icon.purple { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

.stat-info h3 {
    margin: 0 0 5px 0;
    color: #6b7280;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
}
.stat-info .value {
    margin: 0;
    font-size: 28px;
    font-weight: 800;
    color: #111827;
}
.dashboard-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    animation: fadeIn 0.6s ease;
}
@media (max-width: 1024px) {
    .dashboard-content { grid-template-columns: 1fr; }
}
.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 2px solid #f3f4f6;
    padding-bottom: 12px;
}
.section-title i { color: #3b82f6; }

/* Reusing .task-card from style.css but with adjustments for dashboard */
.dash-task-list { display: flex; flex-direction: column; gap: 15px; }
.dash-task-item {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    border: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: transform 0.2s ease;
}
.dash-task-item:hover { transform: translateX(5px); border-color: #e5e7eb; }
.dash-task-info h4 { margin: 0 0 8px 0; font-size: 16px; color: #111827; }
.dash-task-info p { margin: 0; font-size: 13px; color: #6b7280; }
.dash-task-meta { text-align: right; }
.dash-task-meta .deadline { font-size: 13px; font-weight: 600; color: #ef4444; margin-bottom: 8px; display: block; }

.dash-project-card {
    background: linear-gradient(145deg, #1f2937, #111827);
    border-radius: 12px;
    padding: 20px;
    color: white;
    margin-bottom: 15px;
    position: relative;
    overflow: hidden;
}
.dash-project-card h4 { margin: 0 0 10px 0; font-size: 16px; color: #f9fafb; z-index: 2; position: relative; }
.dash-project-card p { margin: 0; font-size: 13px; color: #9ca3af; z-index: 2; position: relative; }
.dash-project-card::after {
    content: ''; position: absolute; top: -50%; right: -20%; width: 150px; height: 150px;
    background: rgba(255,255,255,0.05); border-radius: 50%; z-index: 1;
}

.empty-state {
    text-align: center; padding: 40px 20px; background: white; border-radius: 12px;
    border: 1px dashed #d1d5db; color: #6b7280;
}
</style>

<div class="dashboard-header">
    <h2>Dashboard</h2>
    <p>Oto podsumowanie twojej pracy, <?= htmlspecialchars($_SESSION['username']) ?>.</p>
</div>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon"><i class='bx bx-task'></i></div>
        <div class="stat-info">
            <h3>Wszystkie zadania</h3>
            <p class="value"><?= $totalTasks ?></p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class='bx bx-check-circle'></i></div>
        <div class="stat-info">
            <h3>Zakończone zadania</h3>
            <p class="value"><?= $completedTasks ?></p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class='bx bx-folder'></i></div>
        <div class="stat-info">
            <h3>Projekty</h3>
            <p class="value"><?= $totalProjects ?></p>
        </div>
    </div>
</div>

<div class="dashboard-content">
    <div class="dash-section">
        <h3 class="section-title"><i class='bx bx-time-five'></i> Nadchodzące terminy</h3>
        
        <?php if (empty($upcomingTasks)): ?>
            <div class="empty-state">
                <i class='bx bx-party' style="font-size: 40px; margin-bottom: 10px; color: #9ca3af;"></i><br>
                Brak zadań z nadchodzącym terminem. Świetna robota!
            </div>
        <?php else: ?>
            <div class="dash-task-list">
                <?php foreach ($upcomingTasks as $t): ?>
                    <div class="dash-task-item" style="border-left: 4px solid <?= htmlspecialchars($t['backgroundColor'] ?? '#3b82f6') ?>">
                        <div class="dash-task-info">
                            <h4><?= htmlspecialchars($t['title']) ?></h4>
                            <p>Projekt: <b><?= htmlspecialchars($t['project_name'] ?? 'Brak projektu') ?></b></p>
                        </div>
                        <div class="dash-task-meta">
                            <span class="deadline"><i class='bx bx-calendar'></i> <?= date('d.m.Y H:i', strtotime($t['deadline'])) ?></span>
                            <div class="badges" style="justify-content: flex-end; margin:0;">
                                <span class="badge status-<?= $t['status'] ?>"><?= statusPL_dashboard($t['status']) ?></span>
                                <span class="badge priority-<?= $t['priority'] ?>"><?= priorityPL_dashboard($t['priority']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="dash-section">
        <h3 class="section-title"><i class='bx bx-folder-open'></i> Ostatnie projekty</h3>
        
        <?php if (empty($recentProjects)): ?>
            <div class="empty-state">
                Nie należysz jeszcze do żadnego projektu.
            </div>
        <?php else: ?>
            <div>
                <?php foreach ($recentProjects as $p): ?>
                    <div class="dash-project-card">
                        <h4><?= htmlspecialchars($p['name']) ?></h4>
                        <p>Utworzono: <?= date('d.m.Y', strtotime($p['created_at'])) ?></p>
                        <p style="margin-top: 8px;">
                            <a href="index.php?page=project_view&id=<?= $p['id'] ?>" style="color: #60a5fa; text-decoration: none; font-weight: 600; font-size: 13px;">
                                Otwórz projekt &rarr;
                            </a>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>