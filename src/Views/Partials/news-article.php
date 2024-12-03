<?php
if (!isset($article)) {
    die();
}
?>
<article class="news-article">
    <div class="news-article-text">
        <a href="<?php echo $article->getUri(); ?>">
            <h1><?php echo htmlspecialchars($article->getTitle()); ?></h1>
        </a>
        <h2> <?php echo htmlspecialchars($article->getSubTitle()); ?></h2>
    </div>
<?php
    if (!empty($article->getImagePaths())) {
        echo '<div class="news-article-image">
                    <a hre   '. $article->getUri() .'">
                       <img src="' . $article->getImagePaths()[0] . '" alt="Obrázek článku">
                    </a>
                 </div>';
    }
?>
</article>
