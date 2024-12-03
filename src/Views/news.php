<main>
    <div class="container">
        <h1>Novinky</h1>
        <?php
        if (isset($_SESSION['user_data'])) {
            if ($_SESSION['user_data']->getRole() === 'admin' or $_SESSION['user']->getRole() === 'editor' or $_SESSION['user']->getRole() === 'owner') {
                echo '<a href="'. BASE_URL .'/news/add"><button>Přidat článek</button></a>';
            }
        }
        ?>
    </div>
    <div class="container">
        <?php
        if (!isset($articles) or empty($articles)) {
            echo '<p>Nebyly nalezeny žádné články</p>';
        } else {
            foreach ($articles as $article) {
                require ROOT . 'src/Views/Partials/news-article.php';
            }
        }
        ?>
    </div>
</main>


