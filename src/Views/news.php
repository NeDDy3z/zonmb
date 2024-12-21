<?php

use Helpers\UrlHelper;

?>

<main>
    <div class="container">
        <h1>Novinky</h1>
        <div class="news-search">
            <input type="text" class="search" placeholder="Vyhledat článek...">
            <select name="sort" class="sort">
                <option value="" disabled selected>Seřadit podle</option>
                <option value="&sort=title&asc">Nadpis A-Z</option>
                <option value="&sort=title&desc">Nadpis Z-A</option>
                <option value="&sort=created_at&asc">Datum vzestupně</option>
                <option value="&sort=created_at&asc">Datum sestupně</option>
            </select>
        </div>
    </div>
    <div class="container news-articles">
    </div>
    <div class="news-footer">
        <p id="page-news">Strana <span>1</span></p>
        <button class="prev-page">←</button>
        <button class="next-page">→</button>
    </div>
</main>
<script type="module" src="<?= UrlHelper::baseUrl('assets/js/xhr.js') ?>"></script>
<script type="module" src="<?= UrlHelper::baseUrl('assets/js/news.js') ?>"></script>
