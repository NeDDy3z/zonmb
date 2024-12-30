import {sendRequest, sendRequestWithPayload} from "./xhr.js";
import {sendMessageSignal} from "./messageDisplay.js";
import {encodeHtml, prettyDate} from "./utils.js";

const commentsContainer = document.querySelector('.comments-container');

// Get user data
let user;
sendRequest('GET', '../users/me', function (data) {
    if (data.status === 200) {
        user = JSON.parse(data.responseText);
    }
});


// Image slider
document.addEventListener("DOMContentLoaded", function () {
    const slides = document.querySelectorAll(".slide");
    const prevButton = document.querySelector(".prev-button");
    const nextButton = document.querySelector(".next-button");

    let currentSlide = 0;
    const slideCount = slides.length;

    // Function to show a specific slide
    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.toggle("active", i === index);
        });
        currentSlide = index;
    }

    // Show the next slide
    function nextSlide() {
        const nextIndex = (currentSlide + 1) % slideCount;
        showSlide(nextIndex);
    }

    // Show the previous slide
    function prevSlide() {
        const prevIndex = (currentSlide - 1 + slideCount) % slideCount;
        showSlide(prevIndex);
    }

    // Add event listeners for navigation buttons
    nextButton.addEventListener("click", () => {
        nextSlide();
    });

    prevButton.addEventListener("click", () => {
        prevSlide();
    });
});


// Comments display
function displayComments(page) {
    let articleId = document.querySelector('article').id;

    if (!page) {
        page = 1;
    }

    sendRequest('GET', `../comments/get?article_id=${articleId}&page=${page}`, function (data) {
        data = JSON.parse(data.responseText);

        if (data.length !== 0 && data instanceof Array) {
            commentsContainer.innerHTML = ''; // clear

            data.forEach(comment => {
                let commentElement = document.createElement('div');
                commentElement.classList.add('comment');
                commentElement.innerHTML =
                    `<div>
                            <p>${comment.author}</p>
                            <p><span class="grayed-out">${prettyDate(comment.created_at)}</span></p>
                            <p>${encodeHtml(comment.text)}</p>
                    </div>`;

                if (comment.author_id === user.id) {
                    let deleteButton = document.createElement('button');
                    deleteButton.classList.add('danger');
                    deleteButton.innerText = 'Smazat';
                    deleteButton.addEventListener('click', function (event) {
                        event.preventDefault();
                        if (confirm('Jste si jistí?')) {
                            sendRequest('GET', `../comments/delete?id=${comment.id}`, function (data) {
                                let type = (data.response.includes('success')) ? 'success' : 'error';
                                let response = JSON.parse(data.responseText);
                                sendMessageSignal(
                                    type,
                                    (type === 'success') ? response.success : response.error,
                                );
                                displayComments(1);
                            });
                        }
                    });
                    commentElement.appendChild(deleteButton);
                }
                commentsContainer.appendChild(commentElement);
            })
        }
    });

    if (commentsContainer.children.length === 0) {
        let p = document.createElement('p');
        p.innerHTML = `<p>Žádné komentáře</p>`;
        commentsContainer.appendChild(p);
    }
}

// Send comment
function sendComment(url, article, author, text) {
    let data = new FormData();
    data.append('article', article);
    data.append('author', author);
    data.append('comment', text);

    sendRequestWithPayload('POST', url, function (data) {
        let type;
        let response;
        if (data.response.includes('error')) {
            type = (data.response.includes('success')) ? 'success' : 'error';
            response = JSON.parse(data.responseText).error;
        } else {
            type = 'success';
            response = 'commentAdded';
        }

        sendMessageSignal(
            type,
            response,
        );
        displayComments(1);
    }, data);
}

function addEventListenerToPage() {
    const commentsFooter = document.querySelector(`.comments-footer`);
    const nextPage = commentsFooter.querySelector('.next-page');
    const prevPage = commentsFooter.querySelector('.prev-page');
    const pageSpan = commentsFooter.querySelector('span');

    nextPage.addEventListener('click', function () {
        let page = parseInt(pageSpan.textContent);
        if (page <= 1 && commentsContainer.children.length === 10) {
            page++;
            pageSpan.textContent = page;

            displayComments(page);
        }
        displayComments(page);
    })

    prevPage.addEventListener('click', function () {
        let page = parseInt(pageSpan.textContent);
        if (page > 1) {
            page--;

            pageSpan.textContent = page;
            displayComments(page);
        }
        displayComments(page);
    })
}


document.addEventListener('DOMContentLoaded', function () {
    addEventListenerToPage();
    displayComments(1);
});

document.addEventListener('DOMContentLoaded', () => {
    const commentForm = document.querySelector('form[name="commentForm"]');

    // Handle form submission
    commentForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData(commentForm);
        sendComment(commentForm.action, formData.get('article'), formData.get('author'), formData.get('comment'));
        commentForm.reset();
    });
});

