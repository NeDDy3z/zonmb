// Dependencies: xhr.js, utils.js
import {getData} from "./xhr.js";
import {encodeHtml} from "./utils.js";


const newsArticles = document.querySelector('.container.news-articles');

function newsQuery() {
    let query = 'articles/get?'
    let search = document.querySelector('input.search').value;
    let page = document.querySelector('#page-news').querySelector('span').textContent ?? 1;

    query += (search) ? '&search=' + search : '';
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
                    </div>
                    <div class="news-article-image">
                        <a href="'. UrlHelper::baseUrl('articles/'. $article->getSlug()) .'">
                            <img src="${article.image_paths[0]}" alt="Obrázek článku">
                        </a>
                    </div>`;

            newsArticles.appendChild(articleElement);
        })
    }

    if (newsArticles.innerHTML === '') {
        let p = document.createElement('p');
        p.innerText = 'Nebyly nalezeny žádné články';
        newsArticles.appendChild(p);
    }
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

document.querySelector('.search').addEventListener('input', function (e) {
    e.preventDefault();
    fetchAndLoadArticles();
})

fetchAndLoadArticles();