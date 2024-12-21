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
                    <th><a href="#" class="sort" data-sort="id">ID</a></th>
                    <th><a href="#" class="sort" data-sort="username">Uživatelské jméno</a></th>
                    <th><a href="#" class="sort" data-sort="fullname">Celé jméno</a></th>
                    <th><a href="#" class="sort" data-sort="role">Role</a></th>
                    <th><a href="#" class="sort" data-sort="created_at">Datum registrace</a></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div class="table-footer">
                <p id="page-users">Strana x/x</p>
                <button id="prev-page-users">←</button>
                <button id="next-page-users">→</button>
            </div>
        </section>
        <section class="table-data table-articles">
            <div class="table-header">
                <h2>Články</h2>
                <a href="<?= UrlHelper::baseUrl('articles/add') ?>"><button>Přidat článek</button></a>
                <input type="text" class="search" placeholder="Vyhledat článek...">
            </div>
            <p id="warning-display-articles">Zobrazení článků není podporované, kvůli množštví informací, v takto uzkem formátu, zkuste otočtit prosím zařízení na šířku, nebo použijte počítač.</p>
            <table class="articles-table">
                <thead>
                <tr>
                    <th><a href="#" class="sort" data-sort="id">ID</a></th>
                    <th><a href="#" class="sort" data-sort="title">Nadpis</a></th>
                    <th><a href="#" class="sort" data-sort="subtitle">Podnadpis</a></th>
                    <th><a href="#" class="sort" data-sort="content">Obsah</a></th>
                    <th><a href="#" class="sort" data-sort="images">Obrázky</a></th>
                    <th><a href="#" class="sort" data-sort="author">Autor</a></th>
                    <th><a href="#" class="sort" data-sort="created_at">Datum zveřejnění</a></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div class="table-footer">
                <p id="page-articles">Strana x/x</p>
                <button id="prev-page-articles">←</button>
                <button id="next-page-articles">→</button>
            </div>
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
<script src="<?= UrlHelper::baseUrl('assets/js/table.js') ?>"></script>
<script src="<?= UrlHelper::baseUrl('assets/js/overlay.js') ?>"></script>