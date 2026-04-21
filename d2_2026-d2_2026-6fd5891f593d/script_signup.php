<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?page=rejestrowanie");
    exit();
}

$user       = trim($_POST['username']);
$first_name = trim($_POST['first_name']);
$last_name  = trim($_POST['last_name']);
$pass       = $_POST['password'];
$email      = trim($_POST['email']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: index.php?page=rejestrowanie&error=invalid_email");
    exit();
}

try {
    $dbh = new PDO("mysql:host=localhost;dbname=systemzarzadzania", "root", "");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $dbh->prepare("SELECT login, email FROM users WHERE login = ? OR email = ?");
    $stmt->execute([$user, $email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        if ($existingUser['login'] === $user) $error = "user_exists"; 
        elseif ($existingUser['email'] === $email) $error = "email_exists";
        else $error = "default";
        header("Location: index.php?page=rejestrowanie&error=" . $error);
        exit();
    }

    $hashed_password = hash("sha256", $_POST['password']);

    $insert = $dbh->prepare(
        "INSERT INTO users (login, password, email, first_name, last_name) VALUES (?, ?, ?, ?, ?)"
    );

    if ($insert->execute([$user, $hashed_password, $email, $first_name, $last_name])) {
        $_SESSION['username'] = $user;
        $_SESSION['user_id']  = $dbh->lastInsertId();
        header("Location: index.php?page=index&reg=success");
    } 
    else header("Location: index.php?page=rejestrowanie&error=db_error"); 
} 
catch (PDOException $e) {
    error_log("Błąd bazy danych: " . $e->getMessage());
    header("Location: index.php?page=rejestrowanie&error=db_error");
    exit();
}