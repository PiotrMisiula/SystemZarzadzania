<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?page=edit_account");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=logowanie");
    exit();
}

$userId   = $_SESSION['user_id'];
$username = trim($_POST['username']);
$password = $_POST['password'];

try {
    $dbh = new PDO("mysql:host=localhost;dbname=systemzarzadzania", "root", "");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if username is already taken by another user
    $sth = $dbh->prepare("SELECT id FROM users WHERE login = :username AND id != :id");
    $sth->bindValue(':username', $username, PDO::PARAM_STR);
    $sth->bindValue(':id', $userId, PDO::PARAM_INT);
    $sth->execute();
    
    if ($sth->fetch()) {
        header("Location: index.php?page=edit_account&error=user_exists");
        exit();
    }

    if (!empty($password)) {
        $inputHash = hash("sha256", $password);
        $sth = $dbh->prepare("UPDATE users SET login = :username, password = :password WHERE id = :id");
        $sth->bindValue(':username', $username, PDO::PARAM_STR);
        $sth->bindValue(':password', $inputHash, PDO::PARAM_STR);
        $sth->bindValue(':id', $userId, PDO::PARAM_INT);
    } else {
        $sth = $dbh->prepare("UPDATE users SET login = :username WHERE id = :id");
        $sth->bindValue(':username', $username, PDO::PARAM_STR);
        $sth->bindValue(':id', $userId, PDO::PARAM_INT);
    }
    $sth->execute();

    $_SESSION['username'] = $username;

    header("Location: index.php?page=edit_account&success=1");

} catch (PDOException $e) {
    error_log("Błąd bazy danych: " . $e->getMessage());
    header("Location: index.php?page=edit_account&error=db_error");
    exit();
}
