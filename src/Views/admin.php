<?php
use Helpers\UrlHelper;

?>

<main>
    <div class="tables">
        <h1>Administrace</h1>
        <p id="warning-display">Tato stránka není podporovaná v portrétním režimu, zkuste otočtit prosím zařízení na šířku, nebo použijte počítač.</p>
        <section class="table-data table-users">
            <div class="table-header">
                <h2>Uživatelé</h2>
                <input type="text" class="search" placeholder="Vyhledat uživatele...">
            </div>
            <table class="users-table">
                <thead>
                <tr>
                    <th><a href="#" class="sort active asc" id="users-id">ID<span> &#9650;</span></a></th>
                    <th><a href="#" class="sort" id="users-username">Uživatelské jméno<span></span></a></th>
                    <th><a href="#" class="sort" id="users-fullname">Celé jméno<span></span></a></th>
                    <th><a href="#" class="sort" id="users-role">Role<span></span></a></th>
                    <th><a href="#" class="sort" id="users-created_at">Datum registrace<span></span></a></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div class="table-footer">
                <p id="page-users">Strana <span>1</span></p>
                <button class="prev-page">←</button>
                <button class="next-page">→</button>
            </div>
        </section>
        <section class="table-data table-articles">
            <div class="table-header">
                <h2>Články</h2>
                <a href="<?= UrlHelper::baseUrl('articles/add') ?>" class="btn">Přidat článek</a>
                <input type="text" class="search" placeholder="Vyhledat článek...">
            </div>
            <p id="warning-display-articles">Zobrazení článků není podporované, kvůli množštví informací, v takto uzkem formátu, zkuste otočtit prosím zařízení na šířku, nebo použijte počítač.</p>
            <table class="articles-table">
                <thead>
                <tr>
                    <th><a href="#" class="sort active asc" id="articles-id">ID<span> &#9650;</span></a></th>
                    <th><a href="#" class="sort" id="articles-title">Nadpis<span></span></a></th>
                    <th><a href="#" class="sort" id="articles-subtitle">Podnadpis<span></span></a></th>
                    <th><a href="#" class="sort" id="articles-content">Obsah<span></span></a></th>
                    <th><a href="#" class="sort" id="articles-images">Obrázky<span></span></a></th>
                    <th><a href="#" class="sort" id="articles-author">ID Autora<span></span></a></th>
                    <th><a href="#" class="sort" id="articles-created_at">Datum zveřejnění<span></span></a></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div class="table-footer">
                <p id="page-articles">Strana <span>1</span></p>
                <button class="prev-page">←</button>
                <button class="next-page">→</button>
            </div>
        </section>
    </div>
    <div class="overlay">
        <div class="overlay-content">
            <button class="overlay-close">X</button>
            <h1>-</h1>
            <p>-</p>
        </div>
    </div>
</main>
<script type="module" src="<?= UrlHelper::baseUrl('assets/js/xhr.js') ?>"></script>
<script type="module" src="<?= UrlHelper::baseUrl('assets/js/utils.js') ?>"></script>
<script type="module" src="<?= UrlHelper::baseUrl('assets/js/overlay.js') ?>"></script>
<script type="module" src="<?= UrlHelper::baseUrl('assets/js/admin.js') ?>"></script>
