<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?page=logowanie");
    exit();
}

$username   = trim($_POST['username']);
$inputHash  = hash("sha256", $_POST['password']);

try {
    $dbh = new PDO("mysql:host=localhost;dbname=systemzarzadzania", "root", "");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sth = $dbh->prepare(
        "SELECT id, login, password FROM users WHERE login = :username AND password = :password"
    );
    $sth->bindValue(':username', $username, PDO::PARAM_STR);
    $sth->bindValue(':password', $inputHash, PDO::PARAM_STR);
    $sth->execute();

    $result = $sth->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $_SESSION['username'] = $username;
        $_SESSION['user_id']  = $result['id'];
        header("Location: index.php?page=index");
    } else {
        header("Location: index.php?page=logowanie&error=invalid");
    }

} catch (PDOException $e) {
    error_log("Błąd bazy danych: " . $e->getMessage());
    header("Location: index.php?page=logowanie&error=db_error");
    exit();
}