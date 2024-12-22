// Dependencies: xhr.js, utils.js
import {getData} from "./xhr.js";
import {encodeHtml} from "./utils.js";


const newsArticles = document.querySelector('.container.news-articles');

function newsQuery() {
    let query = 'articles/get?'
    let search = document.querySelector('input.search').value;
    let sort = document.querySelector('.sort').value ?? null;
    let page = document.querySelector('#page-news').querySelector('span').textContent ?? 1;

    query += (search) ? '&search=' + search : '';
    query += (sort !== "") ? sort : '';
    query += (page) ? '&page=' + page : '';

    return query;
}

function fetchAndLoadArticles() {
    getData('articles', newsQuery(), function (data) {
        loadArticles(data);
    })
}

function loadArticles(data) {
    newsArticles.innerHTML = '';

    if (data instanceof Array) {
        data.forEach(article => {
            let articleElement = document.createElement('article');
            articleElement.classList.add('article-news');
            articleElement.innerHTML =
                `<div class="news-article-text">
                        <a href="articles/${article.slug}"><h1>${encodeHtml(article.title)}</h1></a>
                        <h2>${encodeHtml(article.subtitle)}</h2>
                    </div>`;
            if (article.image_paths !== "" && article.image_paths !== null) {
                let stop = false; // Print only one thumbnail in case of multiple ones
                article.image_paths.split(',').forEach(image => {
                    if (image.includes('thumbnail') && !stop) {
                        articleElement.innerHTML +=
                            `<div class="news-article-image">
                                <a href="articles/${article.slug}">
                                    <img src="${image}" alt="Obrázek článku">
                                </a>
                            </div>`;
                        stop = true;
                    }
                })

            }

            newsArticles.appendChild(articleElement);
        })
    }

    if (newsArticles.innerHTML === '') {
        let p = document.createElement('p');
        p.innerText = 'Nebyly nalezeny žádné články';
        newsArticles.appendChild(p);
    }
}


function addEventListenerToSort() {
    const sortSelect = document.querySelector('.sort');

    sortSelect.addEventListener('change', function () {
        fetchAndLoadArticles()
    })
}

function addEventListenerToPage() {
    const pager = document.querySelector('.news-footer');
    const nextPage = pager.querySelector('.next-page');
    const prevPage = pager.querySelector('.prev-page');
    const pageSpan = pager.querySelector('span');

    nextPage.addEventListener('click', function () {
        let page = parseInt(pageSpan.textContent);
        if (page <= 1 && document.querySelector('.news-articles').children.length === 10) {
            page++;
            pageSpan.textContent = page;

            fetchAndLoadArticles();
        }
    })

    prevPage.addEventListener('click', function () {
        let page = parseInt(pageSpan.textContent);
        if (page > 1) {
            page--;

            pageSpan.textContent = page;
            fetchAndLoadArticles();
        }
    })
}

addEventListenerToPage();
addEventListenerToSort();

const search = document.querySelector('.search');
search.addEventListener('input', function (e) { // Wait one second
    clearTimeout(this.timer);

    this.timer = setTimeout(() => {
        fetchAndLoadArticles();
    }, 1000);
});
search.addEventListener('keydown', function (e) { // On enter search
    if (e.key === 'Enter') {
        fetchAndLoadArticles();
    }
});
search.addEventListener('blur', function () {
    fetchAndLoadArticles();
}); // On lost focus


fetchAndLoadArticles();
