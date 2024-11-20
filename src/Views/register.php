<main>
    <div class="container">
        <form action="./register" method="post" enctype="multipart/form-data" name="registerForm" class="login-register">
            <label for="username">Jméno</label>
            <input type="text" id="username" name="username"
                   minlength="3" maxlength="30" pattern="[a-zA-Z0-9_.]+"
                   title="Jméno musí mít nejméně 3 a maximálně 30 znaků, a může obsahovat pouze písmena, číslice, podrtžítka a tečky"
                   tabindex="1" placeholder="*Jméno" required>

            <label for="password">Heslo</label>
            <input type="password" id="password" name="password"
                   minlength="5" maxlength="50"
                   title="Heslo musí mít minimálně 5 a maximálně 50 znaků, a obsahovat alespoň jedno velké psímeno a číslici"
                   tabindex="2" placeholder="*Heslo" required>

            <label for="password-confirm">Potvrďte heslo</label>
            <input type="password" id="password-confirm" name="password-confirm"
                   minlength="5" maxlength="50"
                   title="Heslo musí mít minimálně 5 a maximálně 50 znaků, a obsahovat alespoň jedno velké psímeno a číslici"
                   tabindex="3" placeholder="*Potvrďte heslo" required>

            <label for="profile-image">Profilová fotka</label>
            <input type="file" id="profile-image" name="profile-image" accept="image/png, image/jpg image/jpeg"
                   title="Obrázek musí mít poměr 1:1, maximálně 500x500px, 1MB a být ve formátu PNG nebo JPG"
                   tabindex="4">

            <button type="submit" tabindex="5">Registrovat se</button>
            <p><span class="grayed-out">* povinná pole</span></p>

            <div class="error-container"></div>
            <div class="success-container"></div>

            <a href="./login">Přihlášení</a>
        </form>
    </div>
</main>
<script src="../assets/js/dataValidation.js"></script>
