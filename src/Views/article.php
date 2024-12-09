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
        <article class="article-page">
            <h1><?= htmlspecialchars($article->getTitle()); ?></h1>
            <h2><?= htmlspecialchars($article->getSubtitle()); ?></h2>
            <section class="article-image"> <?php // TODO: Image slideshow?>
                <?php
                    foreach ($article->getImagePaths() as $img) {
                        echo '<img src="'. $img .'" alt="Ilustrační obrázek článku">';
                    }
?>
            </section>
            <section>
                <p><?= htmlspecialchars($article->getContent()); ?></p>
            </section>
            <section class="article-data">
                <p>
                    <span>
                        Autor:
                    </span>
                    <?php
            try {
                echo htmlspecialchars(User::getUserById($article->getAuthorId())->getFullname());
            } catch (Exception $e) {
                echo htmlspecialchars('Autor neznámý');
            }
?>
                </p>
                <p><?= htmlspecialchars(DateHelper::dateTopPrettyString($article->getCreatedAt())) ?></p>
            </section>
        </article>
        <?php
        if (isset($_SESSION['user_data'])) {
            $user = $_SESSION['user_data'];
            if ($user->isEditor()) {
                echo '<a href="'. UrlHelper::baseUrl('article/edit?article-id='.$article->getId()). '"><button>Upravit článek</button></a>';
            }
        }
?>
    </div>
</main>