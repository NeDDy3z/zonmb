<main>
    <div class="container">
        <form action="./login" method="post" class="login-register">
            <label for="username">Jméno: </label>
            <input type="text" id="username" name="username" minlength="3" maxlength="30" pattern="[a-zA-Z0-9_.]+"
                   title="Jméno může obsahovat pouze písmena, číslice, podrtžítka a tečky"
                   tabindex="1" placeholder="Jméno" required>

            <label for="password">Heslo: </label>
            <input type="password" id="password" name="password"
                   minlength="3" maxlength="100" pattern="^(?=.*[A-Z])(?=.*\d).+$"
                   title="Heslo musí mít minimálně 5 a maximálně 100 znaků, a obsahovat alespoň jedno velké psímeno a číslici"
                   tabindex="2" placeholder="Heslo" required>

            <button type="submit" tabindex="3">Přihlásit se</button>

            <?php
            if (isset($_GET['success'])) {
                $success = $_GET['success'];


                if (str_contains(needle: 'login-success', haystack: $success)) {
                    echo '<p class="success-message">Přihlášení proběhlo úspěšně</p>';
                }
                if (str_contains(needle: 'registration-success', haystack: $success)) {
                    echo '<p class="success-message">Registrace proběhla úspěšně</p>';
                }
                echo "\n";
            }

            if (isset($_GET['error'])) {
                $error = $_GET['error'];
                if (str_contains(needle: 'empty-values', haystack: $error)) {
                    echo '<p class="error-message">Chybí některé údaje</p>';
                }
                if (str_contains(needle: 'invalid-username', haystack:  $error)) {
                    echo '<p class="error-message">Uživatelské jméno neexistuje</p>';
                }
                if (str_contains(needle: 'invalid-password', haystack: $error)) {
                    echo '<p class="error-message">Nespravné heslo</p>';
                }
                echo "\n";
            }
            ?>

            <a href="register">Registrace</a>
        </form>
    </div>
</main>