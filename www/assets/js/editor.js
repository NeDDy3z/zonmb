import { sendRequest } from './xhr.js';
import {sendMessageSignal} from "./messageDisplay";

const article = {
    id: (document.querySelector('input[name="id"]')) ? document.querySelector('input[name="id"]').value : null,
    imagesDiv: document.querySelectorAll('.editor-image'),
    imagesRemoveButtons: document.querySelectorAll('button.remove-image'),
};


// Send request to delete image
function deleteImage(page, id, src) {
    sendRequest('GET', `${page}/delete?id=${id}&image=${encodeURIComponent(src)}`, function (data) {
        // Construct data for custom event
        data = JSON.parse(data.response);

        sendMessageSignal(
            (data.includes('success')) ? 'success' : 'error',
            data,
        );

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
