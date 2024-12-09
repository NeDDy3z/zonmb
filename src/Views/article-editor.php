<?php
use Helpers\UrlHelper;

$type ??= 'add';

?>

<main>
    <div class="container article-editor">
        <form action="<?= UrlHelper::baseUrl('article/'. $type) ?>" method="post" enctype="multipart/form-data" name="articleForm" id="article-form">
            <label for="author-id">*Autorské ID: </label>
            <input type="text" name="author" value="<?= $user->getId() ?>" hidden required>

            <label for="title">*Titulek: </label>
            <input type="text" name="title" placeholder="*Titulek" value="<?= isset($article) ? $article->getTitle() : ''; ?>">

            <label for="subtitle">Podtitulek: </label>
            <input type="text" name="subtitle" placeholder="Podtitulek" value="<?= isset($article) ? $article->getSubtitle() : ''; ?>">

            <label for="content">*Obsah: </label>
            <textarea name="content" id="content" cols="30" rows="10" placeholder="*Obsah" value="<?= isset($article) ? $article->getContent() : ''; ?>"></textarea>

            <label for="image">Obrázek: </label>
            <input type="file" name="image" id="image" accept="image/png, image/jpg, image/jpeg" multiple value=""> <!--TODO: LOAD images -->

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
