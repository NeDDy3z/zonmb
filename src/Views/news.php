<?php

use Helpers\UrlHelper;
use Models\DatabaseConnector;

?>

<main>
    <div class="container">
        <h1>Novinky</h1>
        <div class="news-search">
            <input type="text" class="search" placeholder="Vyhledat článek...">
            <select name="sort" class="sort">
                <option value="" disabled selected>Seřadit podle</option>
                <option value="&sort=title&sortDirection=asc">Nadpis A-Z</option>
                <option value="&sort=title&sortDirection=desc">Nadpis Z-A</option>
                <option value="&sort=created_at&sortDirection=asc">Datum vzestupně</option>
                <option value="&sort=created_at&sortDirection=desc">Datum sestupně</option>
            </select>
        </div>
    </div>
    <div class="container news-articles">
        <!--Placeholder div for displaying articles using JS-->
    </div>
    <div class="news-footer">
        <button class="prev-page">&#10094;</button>
        <p id="page-news"><span>1</span>/<?= ceil(DatabaseConnector::count('article') / 10) ?></p>
        <button class="next-page">&#10095;</button>
    </div>
</main>
<script type="module" src="<?= UrlHelper::baseUrl('assets/js/news.js') ?>"></script>
