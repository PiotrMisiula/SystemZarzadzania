<?php
    session_start();

    $page = $_GET['page'] ?? 'dashboard';

    if (!isset($_SESSION['username']) && $page !== 'logowanie' && $page !== 'rejestrowanie') {
        header("Location: ?page=logowanie");
        exit();
    }
?>
<html>
    <head>
        <meta charset="utf-8">
        <title>System zarz¹dzania</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="tlo">
            <?php include 'menu.php'; ?>
            <div class="content">
                <?php 
                    switch($page)
                    {
                        case 'my_tasks':
                            include 'my_tasks.php';
                            break;
                        
                        case 'projects':
                            include 'projects.php';
                            break;
                        
                        case 'calendar':
                            include 'calendar.php';
                            break;

                        case 'logowanie':
                            include 'logowanie.php';
                            break;

                        case 'rejestrowanie':
                            include 'rejestrowanie.php';
                            break;

                        case 'dashboard':
                            include 'dashboard.php';
                            break;
                    }
                ?>
            </div>  
        </div>
    </body>
</html>