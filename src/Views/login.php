<?php
use Helpers\UrlHelper;

?>

<main>
    <div class="container">
        <form action="<?= UrlHelper::baseUrl('login') ?>" method="post" name="loginForm">
            <h1>Přihlášení</h1>

            <label for="username">*Přezdívka: </label>
            <input type="text" id="username" name="username" minlength="3" maxlength="30" pattern="[a-zA-Z0-9_.]+"
                   title="Jméno může obsahovat pouze písmena, číslice, podrtžítka a tečky"
                   tabindex="1" placeholder="*Přezdívka" required>

            <label for="password">*Heslo: </label>
            <input type="password" id="password" name="password"
                   minlength="5" maxlength="50"
                   title="Heslo musí mít minimálně 5 a maximálně 50 znaků, a obsahovat alespoň jedno velké psímeno a číslici"
                   tabindex="2" placeholder="*Heslo" required>

            <button type="submit" tabindex="3">Přihlásit se</button>
            <p><span class="grayed-out">* povinná pole</span></p>

            <div class="message-container static"></div>

            <a href="<?= UrlHelper::baseUrl('register') ?>">Registrace</a>
        </form>
    </div>
</main>
<script src="<?= UrlHelper::baseUrl('assets/js/loadDataOnRefresh.js') ?>"></script>
