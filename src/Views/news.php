<main>
    <div class="container">
        <?php

        if (!isset($articles) or empty($articles)) {
            echo '<p>Nebyly nalezeny žádné články</p>';
            die();
        }

        foreach ($articles as $article) {
            require '../src/Views/Partials/news-article.php';
        }
        ?>
    </div>
</main>


