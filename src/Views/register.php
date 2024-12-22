<?php

use Helpers\UrlHelper;

?>
<main>
    <div class="container">
        <form action="<?= UrlHelper::baseUrl('register') ?>" method="post" enctype="multipart/form-data" name="registerForm" id="register-form">
            <label for="username">Přezdívka</label>
            <input type="text" id="username" name="username"
                   minlength="3" maxlength="30" pattern="[a-zA-Z0-9_.]+"
                   title="Přezdívka musí mít nejméně 3 a maximálně 30 znaků, a může obsahovat pouze písmena, číslice, podrtžítka a tečky"
                   tabindex="1" placeholder="*Jméno" required>

            <label for="fullname">Celé jméno</label>
            <input type="text" id="fullname" name="fullname"
                   minlength="3" maxlength="30" pattern="[a-zA-ZáčďéěíňóřšťúůýžÁČĎÉĚÍŇÓŘŠŤÚŮÝŽ ]+"
                   title="Jméno musí mít nejméně 3 a maximálně 30 znaků, a může obsahovat pouze písmena a mezery"
                   tabindex="2" placeholder="*Celé jméno" required>

            <label for="password">Heslo</label>
            <input type="password" id="password" name="password"
                   minlength="5" maxlength="50"
                   title="Heslo musí mít minimálně 5 a maximálně 50 znaků, a obsahovat alespoň jedno velké psímeno a číslici"
                   tabindex="3" placeholder="*Heslo" required>

            <label for="password-confirm">Potvrďte heslo</label>
            <input type="password" id="password-confirm" name="password-confirm"
                   minlength="5" maxlength="50"
                   title="Heslo musí mít minimálně 5 a maximálně 50 znaků, a obsahovat alespoň jedno velké psímeno a číslici"
                   tabindex="4" placeholder="*Potvrďte heslo" required>

            <label for="profile-image">Profilová fotka</label>
            <input type="file" id="profile-image" name="profile-image" accept="image/png, image/jpeg"
                   title="Obrázek minimálně 200x200px a maximálně 4000x4000px, 2MB a být ve formátu PNG nebo JPEG"
                   tabindex="5">

            <button type="submit" tabindex="5">Registrovat se</button>
            <p><span class="grayed-out">* povinná pole</span></p>

            <div class="error-container"></div>
            <div class="success-container"></div>

            <a href="<?= UrlHelper::baseUrl('login') ?>">Přihlášení</a>
        </form>
    </div>
</main>
<script src="<?= UrlHelper::baseUrl('assets/js/dataValidation.js') ?>"></script>
<script src="<?= UrlHelper::baseUrl('assets/js/loadDataOnRefresh.js') ?>"></script>