<?php

use Zonmb\Logic\Router;
use Zonmb\Logic\User;

// If is user logged in proceed, else redirect to login page
if (isset($_SESSION['username'])) {
    $_SESSION['cache_time'] = $_SESSION['cache_time'] ?? 0;

    // If user_data have been set and aren't older more than 30 minutes, load them, else pull new from database
    if (isset($_SESSION['user_data']) && (time() - $_SESSION['cache_time'] < 1800)) {
        $user = $_SESSION['user_data'];
    } else {
        try {
            $user = new User($_SESSION['username']);
            $_SESSION['user_data'] = $user;
            $_SESSION['cache_time'] = time();
        } catch (Exception $e) {
            Router::redirect('login', 'error', 'Uživatel s tímto jménem neexistuje');
        }
    }
} else {
    Router::redirect('login', 'error', 'Nejste přihlášení');
}
?>

<main>
    <h1>Uživatelská stránka</h1>
    <div class="container userpage">
        <div id="user-data">
            <div id="user-pfp">
                <img src="<?php echo $user->getImage() ?>" alt="profilový obrázek" draggable="false">
            </div>
            <div id="user-info">
                <h3><?php echo $user->getUsername(); ?></h3>
                <ul>
                    <li>Role: </li>
                    <li><?php echo $user->getRole(); ?></li>
                </ul>
                <ul>
                    <li>Datum vytvoření účtu: </li>
                    <li><?php echo $user->getCreatedAt(); ?></li>
                </ul>
            </div>
        </div>

        <a href="./logout"><button type="button">Odhlásit se</button></a>
    </div>
    <div class="container userpage">
        <h3>Změna uživatelského jména</h3>
        <form action="./user/name" method="post">
            <label for="username">Jméno</label>
            <input type="text" id="username" name="username"
                   minlength="3" maxlength="30" pattern="[a-zA-Z0-9_.]+"
                   title="Jméno musí mít nejméně 3 a maximálně 30 znaků, a může obsahovat pouze písmena, číslice, podrtžítka a tečky"
                   tabindex="1" placeholder="Nové uživatelské jméno" required>
            
            <button type="submit">Změnit jméno</button>
        </form>
    </div>
</main>