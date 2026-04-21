<link rel="stylesheet" href="logowanie.css">

<div class="login-wrapper">
    <h2>Zaloguj się</h2>

    <?php if (!empty($_GET['error'])): ?>
        <div class="login-error">
            <?php
                $errors = [
                    'invalid'   => 'Nieprawidłowy login lub hasło.',
                    'db_error'  => 'Wystąpił błąd bazy danych. Spróbuj ponownie.',
                    'default'   => 'Wystąpił nieznany błąd. Spróbuj ponownie.',
                ];
                $key = htmlspecialchars($_GET['error']);
                echo $errors[$key] ?? $errors['default'];
            ?>
        </div>
    <?php endif; ?>

    <form action="script_verification.php" method="POST">
        <div class="login-field">
            <label>Nazwa użytkownika</label>
            <input type="text" name="username" required maxlength="32"
                value="<?= htmlspecialchars($_GET['username'] ?? '') ?>">
        </div>

        <div class="login-field login-field--last">
            <label>Hasło</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="login-btn">Zaloguj się</button>
    </form>

    <p class="login-register-link">
        Nie masz konta?
        <a href="index.php?page=rejestrowanie">Zarejestruj się</a>
    </p>
</div>