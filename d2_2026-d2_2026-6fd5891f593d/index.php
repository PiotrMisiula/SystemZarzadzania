<?php
    session_start();

    $page = $_GET['page'] ?? 'dashboard';

    if (!isset($_SESSION['username']) && $page !== 'logowanie' && $page !== 'rejestrowanie' && $page !== 'new_password') {
        header("Location: ?page=logowanie");
        exit();
    }
?>
<html>
    <head>
        <meta charset="utf-8">
        <title>System zarządzania</title>
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="https://uicdn.toast.com/calendar/latest/toastui-calendar.min.css">
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

                        case 'new_password':
                            include 'new_password.php';
                            break;
                    }
                ?>
            </div>  
        </div>
    </body>
</html>