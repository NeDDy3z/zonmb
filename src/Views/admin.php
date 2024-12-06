<?php

use Helpers\UrlHelper;
use Logic\Router;

if (!isset($_SESSION['user_data'])) {
    Router::redirect(path: 'user', query: ['error' => 'loggedOut']);
}

if (!$_SESSION['user_data']->isAdmin()) {
    Router::redirect(path: 'user', query: ['error' => 'noPrivileges']);
}

?>

<main>
    <div class="tables">
        <h1>Administrace</h1>
        <p id="warning-display">Tato stránka není podporovaná v portrétním režimu, zkuste otočtit prosím zařízení na šířku, nebo použijte počítač.</p>
        <section class="table-data">
            <h2>Uživatelé</h2>
            <table class="users-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Uživatelské jméno</th>
                    <th>Celé jméno</th>
                    <th>Role</th>
                    <th>Datum registrace</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (empty($users)) {
                    echo '<tr><td colspan="5">Žádní uživatelé nenalezeni</td></tr>';
                } else {
                    foreach ($users as $user) {
                        echo '<tr id="user-'. $user->getId() .'">
                                <td>'. $user->getId() .'</td>
                                <td>'. htmlspecialchars($user->getUsername()) .'</td>
                                <td>'. htmlspecialchars($user->getFullname()) .'</td>
                                <td class="editable role">'. htmlspecialchars($user->getRole()) .'</td>
                                <td>'. htmlspecialchars($user->getCreatedAt()) .'</td>
                                <td class="button"><button>Upravit</button></td>
                            </tr>';
                    }
                }
?>
                </tbody>
            </table>
        </section>
        <section class="table-data">
            <h2>Články</h2>
            <p id="warning-display-articles">Zobrazení článků není podporované, kvůli množštví informací, v takto uzkem formátu, zkuste otočtit prosím zařízení na šířku, nebo použijte počítač.</p>
            <table class="articles-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nadpis</th>
                    <th>Podnadpis</th>
                    <th>Obsah</th>
                    <th>Obrázky</th>
                    <th>Autor</th>
                    <th>Datum zveřejnění</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (empty($articles)) {
                    echo '<tr><td colspan="5">Žádné články nenalezeny</td></tr>';
                } else {
                    foreach ($articles as $article) {
                        echo '<tr>
                                <td>'. $article->getId() .'</td>
                                <td>'. htmlspecialchars($article->getTitle()) .'</td>
                                <td>'. htmlspecialchars($article->getSubtitle()) .'</td>
                                <td>'. htmlspecialchars($article->getContent()) .'</td>
                                <td>'. implode(',', $article->getImagePaths()) .'</td> <!--TODO: ADD IMAGE PREVIEW-->
                                <td>'. $article->getAuthorId() .'</td>
                                <td>'. $user->getCreatedAt() .'</td>
                                <td class="button"><a href="'. UrlHelper::baseUrl('article/edit?id='. $article->getId()) .'"><button>Upravit</button></a></td>
                            </tr>';
                    }
                }
?>
                </tbody>
            </table>
        </section>
    </div>
    <div class="overlay">
        <div class="overlay-content">
            <button class="overlay-close">X</button>
            <h1></h1>
            <p></p>
        </div>
    </div>
</main>
<script src="<?= UrlHelper::baseUrl('assets/js/tableDataEditing.js') ?>"></script>