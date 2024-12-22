<?php
use Helpers\UrlHelper;

?>

<main>
    <div class="container article-editor">
        <form action="<?= UrlHelper::baseUrl('users/edit') ?>" method="post" enctype="multipart/form-data" name="articleForm" class="article-form">
            <label for="fullname">Celé jméno</label>
            <input type="text" id="fullname" name="fullname"
                   minlength="3" maxlength="30" pattern="[a-zA-ZáčďéěíňóřšťúůýžÁČĎÉĚÍŇÓŘŠŤÚŮÝŽ ]+"
                   title="Jméno musí mít nejméně 3 a maximálně 30 znaků, a může obsahovat pouze písmena a mezery"
                   tabindex="2" placeholder="*Celé jméno" required>

            <label for="role">Role</label>
            <select name="role" id="role" tabindex="3" required>
                <option value="user">User</option>
                <option value="editor">Editor</option>
                <option value="admin">Admin</option>
            </select>

            <label for="profile-image">Profilová fotka</label>
            <input type="file" id="profile-image" name="profile-image" accept="image/png, image/jpg, image/jpeg"
                   title="Obrázek musí mít minimálně 200x200px a maximálně 4000x4000px, 2MB a být ve formátu PNG nebo JPG"
                   tabindex="5">

            <button type="submit">Upravit</button>
            <p><span class="grayed-out">* povinná pole</span></p>

            <div class="error-container"></div>
            <div class="success-container"></div>

            <a href="<?= UrlHelper::baseUrl('admin') ?>">Administrátorská stránka</a>
        </form>
    </div>
</main>
<script src="<?= UrlHelper::baseUrl('assets/js/dataValidation.js') ?>"></script>
<script src="<?= UrlHelper::baseUrl('assets/js/loadDataOnRefresh.js') ?>"></script>
<script src="<?= UrlHelper::baseUrl('assets/js/editor.js') ?>"></script>
