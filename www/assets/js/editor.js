import { sendRequest } from './xhr.js';

const article = {
    id: (document.querySelector('input[name="id"]')) ? document.querySelector('input[name="id"]').value : null,
    imagesDiv: document.querySelectorAll('.editor-image'),
    imagesRemoveButtons: document.querySelectorAll('button.remove-image'),
};


// Send request to delete image
function deleteImage(page, id, src) {
    let url = `${page}/delete?id=${id}&image=${encodeURIComponent(src)}`;
    sendRequest('GET', url, function (xhr) {
        let data = JSON.parse(xhr.responseText);
        let customEvent = new CustomEvent('infoMessage', {
            detail: {
                type: 'error',
                message: data,
                container: 'popup',
            }
        });
        window.dispatchEvent(customEvent);

        article.imagesDiv.forEach(div => { // Remove image
            let imgSrc = div.querySelector('img').src
            if (new URL(imgSrc).pathname === src) {
                div.remove();
                displayNoImagesMessage();
            }
        });
    });
}

// Display message if no images are present
function displayNoImagesMessage() {
    let editorImagesContainer = document.querySelector('.editor-images-container');
    let editorImages = document.querySelectorAll('.editor-image');
    if (editorImages.length === 0 || editorImagesContainer.children.length <= 1) {
        editorImagesContainer.querySelector('p').style.display = 'block';
    }
}

// Add event listener to remove image buttons
article.imagesRemoveButtons.forEach(button => {
    button.addEventListener('click', () => {
        if (confirm('Opravdu chcete smazat tento obrázek?')) { // Confirm
            let page = (window.location.href.includes('users')) ? 'users' : 'articles';
            deleteImage(`../${page}`, button.id, button.value);
        }
    });
});

// Display message if no images are present
displayNoImagesMessage();
