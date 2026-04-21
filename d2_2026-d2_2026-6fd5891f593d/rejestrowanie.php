<link rel="stylesheet" href="rejestrowanie.css">

<div class="register-wrapper">
    <h2>Zarejestruj się</h2>

    <?php if (!empty($_GET['error'])): ?>
        <div class="register-error">
            <?php
                $errors = [
                    'user_exists'   => 'Podana nazwa użytkownika jest już zajęta.',
                    'email_exists'  => 'Podany adres e-mail jest już zarejestrowany.',
                    'invalid_email' => 'Wprowadzony adres e-mail ma niepoprawny format.', // Nowy błąd
                    'db_error'      => 'Wystąpił błąd bazy danych. Spróbuj ponownie.',
                    'default'       => 'Wystąpił nieznany błąd. Spróbuj ponownie.',
                ];
                $key = htmlspecialchars($_GET['error']);
                echo $errors[$key] ?? $errors['default'];
            ?>
        </div>
    <?php endif; ?>

    <form action="script_signup.php" method="POST">
        <div class="register-field">
            <label>Imię</label>
            <input type="text" name="first_name" required
                value="<?= htmlspecialchars($_GET['first_name'] ?? '') ?>">
        </div>

        <div class="register-field">
            <label>Nazwisko</label>
            <input type="text" name="last_name" required
                value="<?= htmlspecialchars($_GET['last_name'] ?? '') ?>">
        </div>

        <div class="register-field">
            <label>Nazwa użytkownika</label>
            <input type="text" name="username" required maxlength="32"
                value="<?= htmlspecialchars($_GET['username'] ?? '') ?>">
        </div>

        <div class="register-field">
            <label>Adres e-mail</label>
            <input type="email" name="email" required maxlength="64"
                value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
        </div>

        <div class="register-field register-field--last">
            <label>Hasło</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="register-btn">Zarejestruj się</button>
    </form>

    <p class="register-login-link">
        Masz już konto?
        <a href="index.php?page=logowanie">Zaloguj się</a>
    </p>
</div>