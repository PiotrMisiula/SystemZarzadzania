<?php
$page = $_GET['page'] ?? 'dashboard';
$projectsActive =
    $page === 'projects' ||
    $page === 'project_view';
?>
<nav class="navbar">
    <div class="logo">System zarządzania zadaniami</div>

    <?php if (isset($_SESSION['username'])): ?>
        <div class="nav-links">
            <span class="welcome">Witaj, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
            <a class="<?= $page == 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard">Dashboard</a>
            <a class="<?= $page == 'my_tasks' ? 'active' : '' ?>" href="index.php?page=my_tasks">Zadania</a>
            <a class="<?= $projectsActive ? 'active' : '' ?>" href="index.php?page=projects">Projekty</a>
            <a class="<?= $page == 'calendar' ? 'active' : '' ?>" href="index.php?page=calendar">Kalendarz</a>
            <a class="<?= $page == 'edit_account' ? 'active' : '' ?>" href="index.php?page=edit_account">Konto</a>
            <a href="logout.php">Wyloguj się</a>
        </div>
    <?php endif; ?>
</nav>