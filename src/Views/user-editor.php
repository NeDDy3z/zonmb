<?php

use Helpers\UrlHelper;

?>

<main>
    <div class="container editor" id="user-editor">
        <form action="<?= UrlHelper::baseUrl('user/edit') ?>" method="post" enctype="multipart/form-data"
              name="userEditForm">
            <h1>Úprava uživatele</h1>

            <label for="id" class="visible">ID Uživatele</label>
            <input type="text" name="id" id="id" value="<?= isset($editedUser) ? $editedUser->getId() : ''; ?>"
                   readonly>

            <label for="username" class="visible">Přezdívka </label>
            <input type="text" id="username" name="username" minlength="3" maxlength="30" pattern="[a-zA-Z0-9_.]+"
                   title="Přezdívku není možné upravit, protože se pomocí jí uživatel přihlašuje"
                   tabindex="1" placeholder="*Jméno"
                   value="<?= isset($editedUser) ? htmlspecialchars($editedUser->getUsername()) : ''; ?>" readonly
                   required>

            <label for="fullname" class="visible">Celé jméno</label>
            <input type="text" id="fullname" name="fullname"
                   minlength="3" maxlength="30" pattern="[a-zA-ZáčďéěíňóřšťúůýžÁČĎÉĚÍŇÓŘŠŤÚŮÝŽ ]+"
                   title="Jméno musí mít nejméně 3 a maximálně 30 znaků, a může obsahovat pouze písmena a mezery"
                   tabindex="2" placeholder="*Celé jméno"
                   value="<?= isset($editedUser) ? htmlspecialchars($editedUser->getFullname()) : ''; ?>" required>

            <label for="role" class="visible">Role</label>

            <?php
            if (isset($userRole)) {
                $isOwner = ($editedUser->getRole() === 'owner');
                $disabled = ($isOwner) ? 'disabled' : '';

                // Start the select element
                echo "<select name='role' id='role' tabindex='3' $disabled required>";

                // Add a placeholder option as the first option
                echo "<option value='' disabled" . (empty($editedUser->getRole()) ? " selected" : "") . ">Vyberte roli</option>";

                // Generate the rest of the options
                foreach ($userRole as $key => $value) {
                    if (!$isOwner and $key === 'owner') {
                        continue;
                    }
                    $selected = ($key === $editedUser->getRole()) ? " selected" : "";
                    echo "<option value='$key' $selected>$value</option>";
                }
                echo '</select>';
            } ?>

            <h3>Profilový obrázek</h3>
            <div class="user-image editor-images-container">
                <p><span class="grayed-out">Uživatel nemá nahraný žádný profilový obrázek</span></p>
                <?php if (isset($editedUser) and $editedUser->getImage() !== DEFAULT_PFP) {
                    $image = $editedUser->getImage();
                    echo '<div class="editor-image">
                                <button type="button" class="danger remove-image" value="' . UrlHelper::baseUrl($image) . '" id="' . $editedUser->getId() . '">X</button>
                                <img src="' . UrlHelper::baseUrl($image) . '" alt="Profilový obrázek uživatele">
                          </div>';
                }
                ?>
            </div>

            <button type="submit">Upravit</button>
            <p><span class="grayed-out">* povinná pole</span></p>

            <div class="message-container static"></div>

            <a href="<?= UrlHelper::baseUrl('admin') ?>">Administrátorská stránka</a>
        </form>
    </div>
</main>
<script src="<?= UrlHelper::baseUrl('assets/js/loadDataOnRefresh.js') ?>"></script>
<script type="module" src="<?= UrlHelper::baseUrl('assets/js/editor.js') ?>"></script>
