<?php
use Helpers\UrlHelper;

?>

<script type="module" src="<?= UrlHelper::baseUrl('assets/js/xhr.js') ?>"></script>
<script type="module" src="<?= UrlHelper::baseUrl('assets/js/dataValidation.js') ?>"></script>
<script type="module" src="<?= UrlHelper::baseUrl('assets/js/messageDisplay.js') ?>"></script>
<footer>
    <p>&copy; <?= date('Y'); ?> | Erik Vaněk<p>
</footer>
</body>
</html>
