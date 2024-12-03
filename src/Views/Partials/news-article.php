<?php
if (!isset($article)) {
    die();
}
?>
<article class="news-article">
    <div class="news-article-text">
        <a href="<?= BASE_URL . $article->getUri(); ?>">
            <h1><?= htmlspecialchars($article->getTitle()); ?></h1>
        </a>
        <h2> <?= htmlspecialchars($article->getSubTitle()); ?></h2>
    </div>
<?php
    if (!empty($article->getImagePaths())) {
        echo '<div class="news-article-image">
                    <a href="'. htmlspecialchars($article->getUri()) .'">
                       <img src="' . htmlspecialchars($article->getImagePaths()[0]) . '" alt="Obrázek článku">
                    </a>
                 </div>';
    }
?>
</article>
