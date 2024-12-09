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


