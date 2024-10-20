<main>
    <div class="container">
        <form action="./register" method="post" class="login-register">
            <label for="username">Jméno</label>
            <input type="text" id="username" name="username"
                   minlength="3" maxlength="30" pattern="[a-zA-Z0-9_.]+"
                   title="Jméno musí mít nejméně 3 a maximálně 30 znaků, a může obsahovat pouze písmena, číslice, podrtžítka a tečky"
                   tabindex="1" placeholder="Jméno" required>

            <label for="password">Heslo</label>
            <input type="password" id="password" name="password"
                   minlength="5" maxlength="100" pattern="^(?=.*[A-Z])(?=.*\d).+$"
                   title="Heslo musí mít minimálně 5 a maximálně 100 znaků, a obsahovat alespoň jedno velké psímeno a číslici"
                   tabindex="2" placeholder="Heslo" required>

            <label for="password-confirm">Potvrďte heslo</label>
            <input type="password" id="password-confirm" name="password-confirm"
                   minlength="5" maxlength="100" pattern="^(?=.*[A-Z])(?=.*\d).+$"
                   title="Heslo musí mít minimálně 5 a maximálně 100 znaků, a obsahovat alespoň jedno velké psímeno a číslici"
                   tabindex="3" placeholder="Potvrďte heslo" required>

            <label for="profile-image">Profilová fotka</label>
            <input type="file" id="profile-image" name="profile-image" accept="image/png, image/jpg"
                   title="Obrázek musí mít poměr 1:1, maximálně 500x500px, 1MB a být ve formátu PNG nebo JPG" tabindex="4">

            <button type="submit" tabindex="5">Registrovat se</button>


            <?php
            if (isset($_GET['error'])) {
                $error = $_GET['error'];

                if (str_contains(needle: 'empty-values', haystack: $error)) {
                    echo '<p class="error-message">Chybí některé údaje</p>';
                }
                if (str_contains(needle: 'invalid-username-regex', haystack:  $error)) {
                    echo '<p class="error-message">Jméno může obsahovat pouze následující znaky: a-z A-Z 0-9 . _</p>';
                }
                if (str_contains(needle: 'invalid-username-size', haystack:  $error)) {
                    echo '<p class="error-message">Jméno musí mít délku minimálně 3 a maxilmálně 30 znkaů</p>';
                }
                if (str_contains(needle: 'username-taken', haystack:  $error)) {
                    echo '<p class="error-message">Uživatelské jméno již existuje</p>';
                }
                if (str_contains(needle: 'invalid-password-size', haystack: $error)) {
                    echo '<p class="error-message">Heslo musí být dlouhé minimálně 5 a maximálně 100 znaků</p>';
                }
                if (str_contains(needle: 'invalid-password-regex', haystack: $error)) {
                    echo '<p class="error-message">Heslo musí bsahovat alespoň jedno velké písmeno a číslici</p>';
                }
                if (str_contains(needle: 'passwords-dont-match', haystack: $error)) {
                    echo '<p class="error-message">Hesla se musí schodovat</p>';
                }
                echo "\n";
            }
            ?>

            <a href="login">Přihlášení</a>
        </form>

    </div>
</main>