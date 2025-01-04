<?php

use Helpers\DateHelper;
use Helpers\UrlHelper;
use Logic\User;
use Models\DatabaseConnector;

if (!isset($article)) {
    echo 'Článek se nepodařilo načíst';
}
?>

<main>
    <div class="container">
        <?php
        if (isset($_SESSION['user_data'])) {
            $user = $_SESSION['user_data'];
            if ($user->isEditor()) {
                echo '<a href="' . UrlHelper::baseUrl('article/edit?id=' . $article->getId()) . '">Upravit článek</a>';
            }
        }
        ?>
        <article class="article-page" id="<?= $article->getId(); ?>">
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
                        echo htmlspecialchars(User::get(id: $article->getAuthorId())->getFullname());
                    } catch (Exception $e) {
                        echo htmlspecialchars('Autor nenalezen');
                    }
                    ?>
                </p>
                <p><?= htmlspecialchars(DateHelper::toPrettyFormat($article->getCreatedAt())) ?></p>
            </div>
        </article>
        <section class="comments-section">
            <h1>Komentáře</h1>
            <div>
                <form action="<?= UrlHelper::baseUrl('comment/add') ?>" method="post" name="commentForm">
                    <label for="article">ID Článku</label>
                    <input type="text" id="article" name="article" value="<?= $article->getId(); ?>" hidden readonly>
                    <label for="author">ID Autora</label>
                    <input type="text" id="author" name="author" value="<?= $user->getId(); ?>" hidden readonly>

                    <label for="comment">*Komentář</label>
                    <textarea name="comment" id="comment" cols="100" rows="10" minlength="5" maxlength="255"
                              title="Komentář může obsahovat jakékoliv znaky, musí mít délku minimálně 5 a maximálně 255 znaků."
                              placeholder="*Přidat komentář"></textarea>

                    <button type="submit">Odeslat</button>
                </form>
            </div>
            <hr>
            <div class="comments-container">

            </div>
            <div class="comments-footer">
                <button class="prev-page">&#10094;</button>
                <p id="page-comments"><span>1</span>/<?= ceil(DatabaseConnector::count('comment') / 10) ?></p>
                <button class="next-page">&#10095;</button>
            </div>
        </section>

        <a href="<?= UrlHelper::baseUrl('news'); ?>">Zpět na zprávy</a>
    </div>
</main>
<script defer type="module" src="<?= UrlHelper::baseUrl('assets/js/article.js') ?>"></script>

