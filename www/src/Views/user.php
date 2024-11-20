<?php

use Logic\Router;

if (!isset($user)) {
    Router::redirect(path: 'user', query: 'popup', parameters: 'Nepodařilo se načíst uživatelská data, přihlašte se prosím znovu, nebo kontaktujte administrátora.');
}

if (!isset($userRole)) {
    $userRole = [
        'admin' => 'Administrátor',
        'user' => 'Uživatel',
        'owner' => 'Vlastník'
    ];
}


?>


<main>
    <section class="userpage">
        <h1>Uživatelská stránka</h1>
        <div class="user-container userinfo">
            <div id="user-data">
                <div id="user-pfp">
                    <img src="<?php echo $user->getImage() ?>" alt="profilový obrázek" draggable="false">
                </div>
                <div id="user-info">
                    <h3><?php echo $user->getUsername(); ?></h3>
                    <ul>
                        <li><i><?php echo $userRole[$user->getRole()]; ?></i></li>
                    </ul>
                    <ul>
                        <li>
                            <span class="grayed-out">
                                registrace<br>
                                <?php
                                try {
                                    $date = new DateTime($user->getCreatedAt());
                                    echo $date->format('d.m.Y');
                                } catch (Exception $e) {
                                    echo 'N/A';
                                }
?>
                            </span>
                        </li>
                    </ul>
                    <a href="./logout">
                        <button type="button" class="warning" id="logout">Odhlásit se</button>
                    </a>
                </div>
            </div>

        </div>
    </section>
    <section class="userpage">
        <div class="user-container user-change">
            <h3>Změna uživatelského jména</h3>
            <form action="./user/name" method="post">
                <label for="username">Jméno</label>
                <input type="text" id="username" name="username"
                       minlength="3" maxlength="30" pattern="[a-zA-Z0-9_.]+"
                       title="Jméno musí mít nejméně 3 a maximálně 30 znaků, a může obsahovat pouze písmena, číslice, podrtžítka a tečky"
                       tabindex="1" placeholder="Nové uživatelské jméno" required>

                <button type="submit" id="change-name">Změnit jméno</button>
            </form>
        </div>
        <div class="user-container user-change">
            <h3>Změna profilové fotky</h3>
            <form action="./user/profile-image" method="post">
                <label for="profile-image">Profilová fotka</label>
                <input type="file" id="profile-image" name="profile-image" accept="image/png, image/jpg"
                       title="Obrázek musí mít poměr 1:1, maximálně 500x500px, 1MB a být ve formátu PNG nebo JPG"
                       tabindex="4">


                <button type="submit" id="change-profile-image">Změnit profilovou fotku</button>
            </form>
        </div>
    </section>
</main>