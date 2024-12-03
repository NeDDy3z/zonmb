<main>
        <div class="container">
            <form action="/news/add" method="post" enctype="multipart/form-data" name="articleForm" class="login-register"> <!-- TODO: Add change styling  -->
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
<script src="assets/js/dataValidation.js"></script>
