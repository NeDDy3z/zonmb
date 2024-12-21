<?php
use Helpers\UrlHelper;

$type ??= 'add';

?>

<main>
    <div class="container article-editor">
        <form action="<?= UrlHelper::baseUrl('articles/'. $type) ?>" method="post" enctype="multipart/form-data" name="articleForm" class="article-form">
            <label for="author-id">*Autorské ID: </label>
            <input type="text" name="author" id="author-id" value="<?= $_SESSION['user_data']->getId() ?>" hidden required>

            <?php if ($type === 'edit') {
                echo '<label for="id">ID Článku: </label>
                      <input type="text" name="id" id="id" value="'. $article->getId() .'" hidden>';
            } ?>

            <label for="title">*Titulek: </label>
            <input type="text" name="title" id="title" placeholder="*Titulek" value="<?= isset($article) ? htmlspecialchars($article->getTitle()) : ''; ?>">

            <label for="subtitle">Podtitulek: </label>
            <input type="text" name="subtitle" id="subtitle" placeholder="Podtitulek" value="<?= isset($article) ? htmlspecialchars($article->getSubtitle()) : ''; ?>">

            <label for="content">*Obsah: </label>
            <textarea name="content" id="content" cols="30" rows="10" placeholder="*Obsah"><?= isset($article) ? htmlspecialchars($article->getContent()) : ''; ?></textarea>

            <label for="images">Přidat obrázky: </label>
            <input type="file" name="images[]" id="images" accept="image/png, image/jpeg"
                   title="Obrázek musí být ve formátu PNG nebo JPEG, můžete nahrát více obrázků najednou"
                   multiple>

            <div class="article-images">
                <?php if (isset($article) and $article->getImagePaths() !== null) {
                    foreach ($article->getImagePaths() as $image) {
                        echo '<div class="article-image">
                                <button type="button" class="danger remove-image" value="'. UrlHelper::baseUrl($image) .'">Odstranit</button>
                                <img src="'. UrlHelper::baseUrl($image) .'" alt="Obrázek článku">
                            </div>';
                    }
                }  ?>
                <p>Žádné obrázky u článku</p>
            </div>

            <button type="submit"><?= $type == 'add' ? 'Zveřejnit' : 'Upravit' ?></button>
            <p><span class="grayed-out">* povinná pole</span></p>

            <div class="error-container"></div>
            <div class="success-container"></div>

            <a href="<?= UrlHelper::baseUrl('admin') ?>">Administrátorská stránka</a>
        </form>
    </div>
</main>
<script src="<?= UrlHelper::baseUrl('assets/js/dataValidation.js') ?>"></script>
<script src="<?= UrlHelper::baseUrl('assets/js/loadDataOnRefresh.js') ?>"></script>
<script src="<?= UrlHelper::baseUrl('assets/js/editor.js') ?>"></script>
