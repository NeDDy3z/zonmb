<?php
use Helpers\UrlHelper;

if (!isset($_SESSION['user_data'])) {
    header('Location: ' . UrlHelper::baseUrl('login'));
    die();
}

$user = $_SESSION['user_data'];

if (!$user->isEditor()) {
    header('Location: ' . UrlHelper::baseUrl('news'));
    die();
}
?>

<main>
        <div class="container">
            <form action="<?= UrlHelper::baseUrl('article/add') ?>" method="post" enctype="multipart/form-data" name="articleForm" id="article-form">
                <label for="author-id">*Autorské ID: </label>
                <input type="text" name="author" value="<?= $user->getId() ?>" hidden required>

                <label for="title">*Titulek: </label>
                <input type="text" name="title" placeholder="*Titulek">

                <label for="subtitle">Podtitulek: </label>
                <input type="text" name="subtitle" placeholder="Podtitulek">

                <label for="content">*Obsah: </label>
                <textarea name="content" id="content" cols="30" rows="10" placeholder="*Obsah"></textarea>

                <label for="image">Obrázek: </label>
                <input type="file" name="image" id="image" accept="image/png, image/jpg, image/jpeg" multiple>

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
