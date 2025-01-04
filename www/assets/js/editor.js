import { sendRequest } from './xhr.js';
import {sendMessageSignal} from "./messageDisplay.js";
import {baseUrl} from "./utils.js";

const article = {
    id: (document.querySelector('input[name="id"]')) ? document.querySelector('input[name="id"]').value : null,
    imagesDiv: document.querySelectorAll('.editor-image'),
    imagesRemoveButtons: document.querySelectorAll('button.remove-image'),
};


// Send request to delete image
function deleteImage(page, id, src) {
    sendRequest('GET', `${page}/delete?id=${id}&image=${encodeURIComponent(src)}`, function (data) {
        // Construct data for custom event
        let type = (data.response.includes('success')) ? 'success' : 'error';
        let response = JSON.parse(data.responseText);
        sendMessageSignal(
            type,
            (type === 'success') ? response.success : response.error,
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
            let page = (window.location.href.includes('user')) ? 'user' : 'article';
            deleteImage(baseUrl(`${page}`), button.id, button.value);
        }
    });
});

// Display message if no images are present
displayNoImagesMessage();
