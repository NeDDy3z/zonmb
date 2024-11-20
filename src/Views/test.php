<main>
    <h1>Testing page</h1>
    <section>
        <h3>Test image upload</h3>
        <form action="./testing/imageupload" method="post" enctype="multipart/form-data">
            <label for="test-image">Image</label>
            <input type="file" id="test-image" name="test-image" accept="image/png" required>
            <button type="submit">Upload</button>
        </form>
        <h5>Uploaded image:</h5>
        <div class="test-image-container">
            <img src="../assets/uploads/articles/test.png" alt="testing upload image">
        </div>
    </section>
</main>