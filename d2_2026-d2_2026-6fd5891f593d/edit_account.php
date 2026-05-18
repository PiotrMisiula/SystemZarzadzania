<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=logowanie");
    exit();
}

try {
    $dbh = new PDO("mysql:host=localhost;dbname=systemzarzadzania", "root", "");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sth = $dbh->prepare("SELECT login FROM users WHERE id = :id");
    $sth->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $sth->execute();
    
    $user = $sth->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        header("Location: logout.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Błąd bazy danych.";
    exit();
}
?>
<link rel="stylesheet" href="rejestrowanie.css">
<style>
.success-msg {
    color: #155724;
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 14px;
    text-align: center;
}
</style>

<div class="register-wrapper">
    <h2>Edycja konta</h2>

    <?php if (!empty($_GET['error'])): ?>
        <div class="register-error">
            <?php
                $errors = [
                    'user_exists'   => 'Podana nazwa użytkownika jest już zajęta.',
                    'db_error'      => 'Wystąpił błąd bazy danych. Spróbuj ponownie.',
                    'default'       => 'Wystąpił nieznany błąd. Spróbuj ponownie.',
                ];
                $key = htmlspecialchars($_GET['error']);
                echo $errors[$key] ?? $errors['default'];
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="success-msg">
            Dane zostały pomyślnie zaktualizowane.
        </div>
    <?php endif; ?>

    <form action="script_edit_account.php" method="POST">
        <div class="register-field">
            <label>Nazwa użytkownika</label>
            <input type="text" name="username" required maxlength="32"
                value="<?= htmlspecialchars($user['login']) ?>">
        </div>

        <div class="register-field register-field--last">
            <label>Nowe hasło (pozostaw puste, aby nie zmieniać)</label>
            <input type="password" name="password">
        </div>

        <button type="submit" class="register-btn">Zapisz zmiany</button>
    </form>
</div>
