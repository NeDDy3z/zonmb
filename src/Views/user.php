<?php

use Helpers\DateHelper;
use Helpers\UrlHelper;
use Logic\Router;

if (!isset($_SESSION['user_data'])) {
    Router::redirect(path: 'user', query: ['popup' => 'Nepodařilo se načíst uživatelská data, odhlašte se a přihlašte se prosím znovu, nebo kontaktujte administrátora.']);
    exit;
}

if (!isset($userRoles)) {
    $userRoles = [
        'admin' => 'Administrátor',
        'user' => 'Uživatel',
        'owner' => 'Vlastník'
    ];
}

$user = $_SESSION['user_data'];
?>


<main>
    <section class="userpage">
        <h1>Uživatelská stránka</h1>
        <div class="user-container userinfo">
            <div id="user-data">
                <div id="user-pfp">
                    <img src="<?= UrlHelper::baseUrl($user->getImage()) ?>" alt="profilový obrázek" draggable="true">
                </div>
                <div id="user-info">
                    <h3><?= htmlspecialchars($user->getUsername()) .' - '. htmlspecialchars($user->getFullname()) ?></h3>
                    <ul>
                        <li><i><?= $userRoles[$user->getRole()]; ?></i></li>
                    </ul>
                    <ul>
                        <li>
                            <span class="grayed-out">registrace<br>
                                <?= DateHelper::toPrettyFormat($user->getCreatedAt())  ?>
                            </span>
                        </li>
                    </ul>
                    <a href="<?= UrlHelper::baseUrl('users/logout') ?>">
                        <button type="button" class="warning" id="logout">Odhlásit se</button>
                    </a>
                </div>
            </div>

        </div>
    </section>
    <section class="userpage">
        <div class="user-container user-change">
            <form action="<?= UrlHelper::baseUrl('users/edit/fullname') ?>" method="post" class="one-line-form">
                <h2>Změna jména</h2>
                <label for="fullname">Celé jméno</label>
                <input type="text" id="fullname" name="fullname"
                       minlength="3" maxlength="30" pattern="[a-zA-ZáčďéěíňóřšťúůýžÁČĎÉĚÍŇÓŘŠŤÚŮÝŽ ]+"
                       title="Jméno musí mít nejméně 3 a maximálně 30 znaků, a může obsahovat pouze písmena a mezery"
                       tabindex="2" placeholder="Nové jméno" required>

                <button type="submit" id="change-name">Změnit jméno</button>
            </form>
        </div>
    </section>
    <section class="userpage">
        <div class="user-container user-change">
            <form action="<?= UrlHelper::baseUrl('users/edit/profile-image') ?>" method="post" enctype="multipart/form-data" class="one-line-form">
                <h2>Změna profilové fotky</h2>
                <label for="profile-image">Profilová fotka</label>
                <input type="file" id="profile-image" name="profile-image" accept="image/png, image/jpg, image/jpeg"
                       title="Obrázek musí mít poměr 1:1, maximálně 500x500px, 1MB a být ve formátu PNG nebo JPG"
                       required>

                <button type="submit" id="change-profile-image">Změnit profilovou fotku</button>
            </form>
        </div>
    </section>
    <?php
        if ($user->isAdmin()) {
            ?>
            <section class="userpage">
                <div class="user-container user-change">
                    <h3><a href="<?= UrlHelper::baseUrl('admin') ?>"><button title="Stránka s možnostmi upravy uživatelu a članku na jednom mistě.">Admin stránka</button></a></h3>
                </div>
            </section>
            <?php
        }
?>
</main>
<script src="<?= UrlHelper::baseUrl('assets/js/dataValidation.js') ?>"></script>
<script src="<?= UrlHelper::baseUrl('assets/js/loadDataOnRefresh.js') ?>"></script>