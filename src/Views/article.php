<?php
use Logic\Article;

if (!isset($article)) {
    echo 'Článek se nepodařilo načíst';
    die();
}
?>

<main>
    <article>
        <h1><?= htmlspecialchars($article->getTitle()); ?></h1>
        <h2><?= htmlspecialchars($article->getSubtitle()); ?></h2>
        <section> <?php // TODO: Image slideshow?>
            <img src="<?= $article->getImagePaths()[0] ?>" alt="Ilustrační obrázek článku">
        </section>
        <section>
            <p><?= htmlspecialchars($article->getContent()); ?></p>
        </section>
        <section>
            <p><?= htmlspecialchars((string)$article->getAuthorId()) ?></p>
            <p><?= htmlspecialchars($article->getCreatedAt()) ?></p>
        </section>
    </article>
</main>