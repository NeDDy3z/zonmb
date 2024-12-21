<?php

use Helpers\UrlHelper;

?>

<main>
    <div class="container">
        <h1>Novinky</h1>
        <?php
        if (isset($_SESSION['user_data'])) {
            $user = $_SESSION['user_data'];

            switch ($user->getRole()) {
                case 'admin':
                case 'editor':
                case 'owner':
                    echo '<a href="'. UrlHelper::baseUrl('articles/add').'"><button>Přidat článek</button></a>';
            }
        }
        ?>
        <input type="text" class="search" placeholder="Vyhledat článek...">
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
