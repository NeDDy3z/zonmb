<?php

use Helpers\UrlHelper;

if (!isset($article)) {
    echo 'Článek se nepodařilo načíst';
    die();
}

?>
<article class="article-news">
    <div class="news-article-text">
        <a href="<?= UrlHelper::baseUrl('articles/'. $article->getSlug()); ?>">
            <h1><?= htmlspecialchars($article->getTitle()); ?></h1>
        </a>
        <h2> <?= htmlspecialchars($article->getSubTitle()); ?></h2>
    </div>
<?php
    if (!empty($article->getImagePaths())) {
        echo '<div class="news-article-image">
                    <a href="'. UrlHelper::baseUrl('article/'. $article->getSlug()) .'">
                       <img src="' . UrlHelper::baseUrl($article->getImagePaths()[0]) . '" alt="Obrázek článku">
                    </a>
              </div>';
    }
?>
</article>
