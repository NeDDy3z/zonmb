const article = {
    id: document.querySelector('input[name="id"]').value,
    imagesDiv: document.querySelectorAll('.article-image'),
    imagesRemoveButtons: document.querySelectorAll('button.remove-image'),
};


function deleteImage(src) {
    const xhr = new XMLHttpRequest();
    xhr.withCredentials = true;
    xhr.open('GET', `delete?id=${article.id}&img=${encodeURIComponent(src)}`, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            article.imagesDiv.forEach(div => {
                let imgSrc = div.querySelector('img').src
                if (new URL(imgSrc).pathname === src) {
                    div.remove();
                }
            });
        }
        document.querySelector('.error-container').innerText += xhr.responseText;
    };
    xhr.send();
}

article.imagesRemoveButtons.forEach(button => {
    button.addEventListener('click', () => {
        deleteImage(button.value);
    });
});
