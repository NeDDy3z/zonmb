const imageEditorContainer = document.querySelector('.image-editor-container');

const article = {
    id: (document.querySelector('input[name="id"]')) ? document.querySelector('input[name="id"]').value : null,
    imagesDiv: document.querySelectorAll('.article-image'),
    imagesRemoveButtons: document.querySelectorAll('button.remove-image'),
};

const user = {
    id: (document.querySelector('input[name="id"]')) ? document.querySelector('input[name="id"]').value : null,
    fullname: (document.querySelector('input[name="fullname"]')) ? document.querySelector('input[name="fullname"]').value : null,
    email: (document.querySelector('input[name="email"]')) ? document.querySelector('input[name="email"]').value : null,
}


// Send request to delete image
function deleteImage(id, src) {
    const xhr = new XMLHttpRequest();
    xhr.withCredentials = true;
    xhr.open('GET', `delete?id=${id}&img=${encodeURIComponent(src)}`, true);
    xhr.onload = function () {
        if (xhr.status === 200 && xhr.responseText.includes('success')) { // If comeback message is successful, remove img locally
            article.imagesDiv.forEach(div => {
                let imgSrc = div.querySelector('img').src
                if (new URL(imgSrc).pathname === src) {
                    div.remove();
                    displayNoImagesMessage();
                }
            });
        }
        document.querySelector('.static-message-container').innerText += xhr.responseText; // TODO: show message
    };
    xhr.send();
}

// Display message if no images are present
function displayNoImagesMessage() {
    if (document.querySelectorAll('.article-image').length === 0) {
        document.querySelector('.article-images').querySelector('p').style.display = 'block';
    }
}

// Add event listener to remove image buttons
article.imagesRemoveButtons.forEach(button => {
    button.addEventListener('click', () => {
        if (confirm('Opravdu chcete smazat tento obrázek?')) { // Confirm
            deleteImage(button.id, button.value);
        }
    });
});

// Display message if no images are present
displayNoImagesMessage();
