<?php
use Helpers\UrlHelper;
use Models\DatabaseConnector;

?>

<main>
    <div class="tables">
        <h1>Administrace</h1>
        <p id="warning-display">Tato stránka není podporovaná v portrétním režimu, prosím, zkuste otočtit zařízení na šířku, nebo použijte počítač.</p>
        <section class="table-data table-user">
            <div class="table-header">
                <h2>Uživatelé</h2>
                <label for="search-users">Vyhledat uživatele</label>
                <input type="text" name="search-users" id="search-users" class="search" placeholder="Vyhledat uživatele...">
            </div>
            <table class="users-table">
                <thead>
                <tr>
                    <th><a href="#" class="sort active asc" id="users-id">ID<span> &#9650;</span></a></th>
                    <th><a href="#" class="sort" id="users-username">Uživatelské jméno<span></span></a></th>
                    <th><a href="#" class="sort" id="users-fullname">Celé jméno<span></span></a></th>
                    <th><a href="#" class="sort" id="users-role">Role<span></span></a></th>
                    <th>Profilový obrázek</th>
                    <th><a href="#" class="sort" id="users-created_at">Datum registrace<span></span></a></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7" class="empty">Načítání...</td>
                    </tr>
                </tbody>
            </table>
            <div class="table-footer">
                <button class="prev-page">&#10094;</button>
                <p id="page-user"><span>1</span>/<?= ceil(DatabaseConnector::count('user') / 10) ?></p>
                <button class="next-page">&#10095;</button>
            </div>
        </section>
        <section class="table-data table-article">
            <div class="table-header">
                <h2>Články</h2>
                <div>
                    <a href="<?= UrlHelper::baseUrl('article/add') ?>" class="btn">Přidat článek</a>
                    <label for="search-articles">Vyhledat článek</label>
                    <input type="text" name="search-articles" id="search-articles" class="search" placeholder="Vyhledat článek...">
                </div>
            </div>
            <p id="warning-display-articles">Zobrazení článků není podporované, kvůli množštví informací, v takto uzkem formátu, zkuste otočtit prosím zařízení na šířku, nebo použijte počítač.</p>
            <table class="articles-table">
                <thead>
                <tr>
                    <th><a href="#" class="sort active asc" id="articles-id">ID<span> &#9650;</span></a></th>
                    <th><a href="#" class="sort" id="articles-title">Nadpis<span></span></a></th>
                    <th><a href="#" class="sort" id="articles-subtitle">Podnadpis<span></span></a></th>
                    <th><a href="#" class="sort" id="articles-content">Obsah<span></span></a></th>
                    <th>Obrázky</th>
                    <th>ID Autora</th>
                    <th><a href="#" class="sort" id="articles-created_at">Datum zveřejnění<span></span></a></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="8" class="empty">Načítání...</td>
                    </tr>
                </tbody>
            </table>
            <div class="table-footer">
                <button class="prev-page">&#10094;</button>
                <p id="page-article"><span>1</span>/<?= ceil(DatabaseConnector::count('article') / 10) ?></p>
                <button class="next-page">&#10095;</button>
            </div>
        </section>
        <section class="table-data table-comment">
            <div class="table-header">
                <h2>Komentáře</h2>
                <div>
                    <label for="search-comments">Vyhledat komentář</label>
                    <input type="text" name="search-comments" id="search-comments" class="search" placeholder="Vyhledat komentář...">
                </div>
            </div>
            <table class="comments-table">
                <thead>
                <tr>
                    <th><a href="#" class="sort active asc" id="comments-id">ID<span> &#9650;</span></a></th>
                    <th><a href="#" class="sort" id="comments-text">Text<span></span></a></th>
                    <th><a href="#" class="sort" id="comments-article_id">ID Článku<span></span></a></th>
                    <th><a href="#" class="sort" id="comments-author_id">ID Autora<span></span></a></th>
                    <th><a href="#" class="sort" id="comments-created_at">Datum zveřejnění<span></span></a></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="empty">Načítání...</td>
                    </tr>
                </tbody>
            </table>
            <div class="table-footer">
                <button class="prev-page">&#10094;</button>
                <p id="page-comment"><span>1</span>/<?= ceil(DatabaseConnector::count('comment') / 10) ?></p>
                <button class="next-page">&#10095;</button>
            </div>
        </section>
    </div>
    <div class="overlay">
        <div class="overlay-content">
            <button class="overlay-close">X</button>
            <p>---</p>
        </div>
    </div>
</main>
<script type="module" src="<?= UrlHelper::baseUrl('assets/js/utils.js') ?>"></script>
<script type="module" src="<?= UrlHelper::baseUrl('assets/js/overlay.js') ?>"></script>
<script type="module" src="<?= UrlHelper::baseUrl('assets/js/admin.js') ?>"></script>
