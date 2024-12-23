<?php

use Helpers\DateHelper;
use Helpers\UrlHelper;
use Logic\User;

if (!isset($article)) {
    echo 'Článek se nepodařilo načíst';
    die();
}
?>

<main>
    <div class="container">
        <?php
        if (isset($_SESSION['user_data'])) {
            $user = $_SESSION['user_data'];
            if ($user->isEditor()) {
                echo '<a href="'. UrlHelper::baseUrl('articles/edit?id='.$article->getId()). '">Upravit článek</a>';
            }
        }
        ?>
        <article class="article-page">
            <h1><?= htmlspecialchars($article->getTitle()); ?></h1>
            <h2><?= htmlspecialchars($article->getSubtitle()); ?></h2>
            <div class="article-image slideshow-container">
                <?php
                if ($article->getImagePaths() !== null) {
                    $displayed = false;
                foreach ($article->getImagePaths() as $i => $img) {
                    if (!str_contains($img, 'thumbnail')) {
                        $active = (!$displayed) ? "active" : "";
                        $displayed = true;
                        $imgUrl = UrlHelper::baseUrl($img);
                        echo "<div class='slide $active'>
                                <img src='$imgUrl' alt='Obrázek článku'>
                              </div>";
                    }
                }
                }
            ?>
                <button class="prev-button">&#10094;</button>
                <button class="next-button">&#10095;</button>
            </div>
            <div>
                <p><?= nl2br(htmlspecialchars($article->getContent())); ?></p>
            </div>
            <div class="article-data">
                <p>
                    <span>
                        Autor:
                    </span>
                    <?php
            try {
                echo htmlspecialchars(User::getUserById($article->getAuthorId())->getFullname());
            } catch (Exception $e) {
                echo htmlspecialchars('Autor nenalezen');
            }
?>
                </p>
                <p><?= htmlspecialchars(DateHelper::toPrettyFormat($article->getCreatedAt())) ?></p>
            </div>
        </article>
        <a href="<?= UrlHelper::baseUrl('news'); ?>">Zpět na zprávy</a>
    </div>
</main>
<script src="<?= UrlHelper::baseUrl('assets/js/imageSlideshow.js') ?>"></script>