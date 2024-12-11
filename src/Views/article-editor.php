<?php
use Helpers\UrlHelper;

$type ??= 'add';

?>

<main>
    <div class="container article-editor">
        <form action="<?= UrlHelper::baseUrl('articles/'. $type) ?>" method="post" enctype="multipart/form-data" name="articleForm" class="article-form">
            <label for="author-id">*Autorské ID: </label>
            <input type="text" name="author" value="<?= $user->getId() ?>" hidden required>

            <?php if ($type === 'edit') {
                echo '<label for="id">ID Článku: </label>
                      <input type="text" name="id" value="'. $article->getId() .'" hidden>';
            } ?>

            <label for="title">*Titulek: </label>
            <input type="text" name="title" placeholder="*Titulek" value="<?= isset($article) ? $article->getTitle() : ''; ?>">

            <label for="subtitle">Podtitulek: </label>
            <input type="text" name="subtitle" placeholder="Podtitulek" value="<?= isset($article) ? $article->getSubtitle() : ''; ?>">

            <label for="content">*Obsah: </label>
            <textarea name="content" id="content" cols="30" rows="10" placeholder="*Obsah"><?= isset($article) ? $article->getContent() : ''; ?></textarea>

            <label for="image">Přidat obrázek: </label>
            <input type="file" name="image" id="image" accept="image/png, image/jpg, image/jpeg"
                   title="Obrázek musí být ve formátu PNG nebo JPG, můžete nahrát více obrázků najednou"
                   multiple>

            <div class="article-images">
                <?php if ($article->getImagePaths() !== null) {
                    foreach ($article->getImagePaths() as $image) {
                        echo '<div class="article-image">
                                <button type="button" class="danger remove-image" value="'. $image .'">Odstranit</button>
                                <img src="'. $image .'" alt="Obrázek článku">
                            </div>';
                    }
                } else {
                    echo '<p>Žádné obrázky u článku</p>';
                } ?>
            </div>

            <button type="submit">Zveřejnit</button>
            <p><span class="grayed-out">* povinná pole</span></p>

            <div class="error-container"></div>
            <div class="success-container"></div>

            <a href="./">Zpět</a>
        </form>
    </div>
</main>
<script src="<?= UrlHelper::baseUrl('assets/js/dataValidation.js') ?>"></script>
<script src="<?= UrlHelper::baseUrl('assets/js/loadDataOnRefresh.js') ?>"></script>
<script src="<?= UrlHelper::baseUrl('assets/js/editor.js') ?>"></script>
