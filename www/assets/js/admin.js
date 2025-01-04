import {sendRequest} from "./xhr.js";
import {sendMessageSignal} from "./messageDisplay.js";
import {openOverlay} from "./overlay.js";
import {baseUrl, encodeHtml, prettyDate} from "./utils.js";

// Get and assign user data
let user;
sendRequest('GET', 'user/me', function (data) {
    if (data.status === 200) {
        user = JSON.parse(data.responseText);
    }
});


// Open details on article-data click
function addOpenOverlay() {
    let overlayItems = document.querySelectorAll('.overlay-item');
    overlayItems.forEach(item => {
        item.addEventListener('click', (e) => {
            openOverlay(item);
        });
    });
}


// Edit data button template
function createEditButton(table, param) {
    let editLink = document.createElement('a');
    editLink.href = table + '?id=' + param;
    editLink.classList.add('edit', 'btn');
    editLink.innerText = 'Upravit';

    return editLink;
}

// Delete data button template
function createDeleteButton(table, param) {
    let deleteButton = document.createElement('button') // Create delete button
    deleteButton.classList.add('delete', 'danger');
    deleteButton.innerText = 'Smazat';
    deleteButton.addEventListener('click', function () {
        let item;
        switch (table) {
            case 'user':
                item = 'uživatele';
                break;
            case 'article':
                item = 'článek';
                break;
            case 'comment':
                item = 'komentář';
                break;
        }

        if (confirm(`Opravdu chcete smazat ${item} s ID: ${param}?`)) {
            sendRequest('GET', `${table}/delete?id=${param}`, function (data) {
                let type = (data.response.includes('success')) ? 'success' : 'error';
                let response = JSON.parse(data.responseText);
                sendMessageSignal(
                    type,
                    (type === 'success') ? response.success : response.error,
                );
            });
            fetchAndLoadData(table);
        }


    });

    return deleteButton;
}


// Load data to table
function loadData(table, data) {
    const tableSection = document.querySelector(`.table-${table}`);
    const tbody = tableSection.querySelector('tbody');
    tbody.innerHTML = '';

    if (data instanceof Array) {
        data.forEach(dataItem => {
            let row = (table === 'user') ? userRow(dataItem) : (table === 'article') ? articleRow(dataItem) : commentsRow(dataItem);

            switch (true) {
                case dataItem.role === 'owner': // Skip on owner
                    break;
                case user.role === 'admin' && dataItem.role === 'admin': // Skip on admin editing admin
                    break;
                case user.role === 'owner' && dataItem.role !== 'owner': // Enable owner editing everyone except owner
                case user.role === 'admin' && dataItem.role !== 'admin': // Enable admin editing everyone except admin
                    let buttonDiv = row.querySelector('.buttons');

                    if (table !== 'comments') {
                        buttonDiv.appendChild(createEditButton(`${table}/edit`, dataItem.id));
                    }
                    buttonDiv.appendChild(createDeleteButton(`${table}`, dataItem.id)); // Append delete button to row
                    break;
            }

            tbody.appendChild(row);
        });
        addOpenOverlay();
    }

    if (tbody.innerText === '') {
        let row = document.createElement('tr');
        row.innerHTML = `<td colspan="8" class="empty">Žádná data nenalezena</td>`;
        tbody.appendChild(row);
    }
}

// Query builder
function tableQuery(table) {
    const tableSection = document.querySelector(`.table-${table}`);

    const search = tableSection.querySelector('.search') ?? null;
    const sortField = tableSection.querySelector('.sort.active') ?? null;
    const sortDirection = (sortField.classList.contains('asc')) ? 'asc' : 'desc' ?? null;
    const page = tableSection.querySelector(`#page-${table}`).querySelector('span').textContent ?? 1;

    let query = `${table}s?`;
    query += (search) ? `search=${search.value}` : '';
    query += (sortField) ? `&sort=${sortField.id.split('-')[1]}` : '';
    query += (sortDirection) ? `&sortDirection=${sortDirection}` : '';
    query += (page) ? `&page=${page}` : '';

    return query;
}

// Single function to fetch and load data
function fetchAndLoadData(table) {
    sendRequest('GET', tableQuery(table), function (data) {
        let response = JSON.parse(data.responseText);
        let type = (data.response.includes('success')) ? 'success' : 'error';

        loadData(table, response);
        sendMessageSignal(
            type,
            (type === 'success') ? response.success : response.error,
        );
    });
}


// Table rows
function userRow(user) {
    let row = document.createElement('tr');
    row.innerHTML = `
            <td>${encodeHtml(user.id)}</td>
            <td>${encodeHtml(user.username)}</td>
            <td>${encodeHtml(user.fullname)}</td>
            <td>${encodeHtml(user.role)}</td>
            <td class="overlay-item overlay-image-item user">${encodeHtml(user.profile_image_path)}</td>
            <td>${encodeHtml(prettyDate(user.created_at))}</td>
            <td class="buttons"></td>`;

    return row;
}

function articleRow(article) {
    let row = document.createElement('tr');
    row.innerHTML = `
            <td><a href="${baseUrl('article/')+encodeURIComponent(`${article.slug}`)}">${encodeHtml(article.id)}</a></td>
            <td class="overlay-item">${encodeHtml(article.title)}</td>
            <td class="overlay-item" >${encodeHtml(article.subtitle)}</td>
            <td class="overlay-item" >${encodeHtml(article.content)}</td>
            <td class="overlay-item overlay-image-item" >${encodeHtml(article.image_paths)}</td>
            <td class="overlay-item" >${encodeHtml(article.author_id)}</td>
            <td class="overlay-item" >${encodeHtml(prettyDate(article.created_at))}</td>
            <td class="buttons"></td>                
        `;

    return row;
}

function commentsRow(comment) {
    let row = document.createElement('tr');
    row.innerHTML = `
            <td>${encodeHtml(comment.id)}</td>
            <td>${encodeHtml(comment.article_id)}</td>
            <td>${encodeHtml(comment.author_id)}</td>
            <td class="overlay-item">${encodeHtml(comment.text)}</td>
            <td>${encodeHtml(prettyDate(comment.created_at))}</td>
            <td class="buttons"></td>                
        `;

    return row;
}


// Sorter
// Toggle sort active - state: false=off, true=on
function toggleSort(sort, state) {
    if (state) {
        sort.classList.add('active');
    } else {
        sort.classList.remove('active');
    }
}

// If sort is active, toggle sort direction
function toggleSortDirection(sort) {
    if (sort.classList.contains('active')) {
        if (sort.classList.contains('asc')) {
            sort.classList.remove('asc');
            sort.classList.add('desc');

            toggleArrow(sort, true);
        } else {
            sort.classList.remove('desc');
            sort.classList.add('asc');

            toggleArrow(sort, false);
        }
    } else {
        toggleArrow(sort, null);
    }
}

// Design change
function toggleArrow(sort, state) {
    let span = sort.querySelector('span');

    switch (state) {
        case true:
            span.innerHTML = ' &#9660'; // ASC
            break;
        case false:
            span.innerHTML = ' &#9650'; // DESC
            break;
        default:
            span.innerHTML = '';
            break;
    }
}


// Event listener
function addEventListenerToSearch(table) {
    const search = document.querySelector(`.table-${table} .search`);
    search?.addEventListener('input', function () {
        fetchAndLoadData(table);
    })
}

function addEventListenerToSort(table) {
    const sortButtons = document.querySelectorAll(`.table-${table} .sort`);

    sortButtons.forEach(button => {
        button.addEventListener('click', function () {
            toggleSort(button, true);
            toggleSortDirection(button);

            let id = button.id;
            sortButtons.forEach(button => {
                if (button.id !== id) {
                    toggleSort(button, false);
                    toggleSortDirection(button);
                }
            })

            fetchAndLoadData(table);
        })
    })
}

function addEventListenerToPage(table) {
    const tableFooter = document.querySelector(`.table-${table} .table-footer`);
    const nextPage = tableFooter.querySelector('.next-page');
    const prevPage = tableFooter.querySelector('.prev-page');
    const pageSpan = tableFooter.querySelector('span');
    const tbody = document.querySelector(`.table-${table} tbody`);

    nextPage.addEventListener('click', function () {
        let page = parseInt(pageSpan.textContent);
        if (page <= 1 && tbody.children.length === 10) {
            page++;
            pageSpan.textContent = page;

            fetchAndLoadData(table);
        }
    })

    prevPage.addEventListener('click', function () {
        let page = parseInt(pageSpan.textContent);
        if (page > 1) {
            page--;

            pageSpan.textContent = page;
            fetchAndLoadData(table);
        }
    })
}

// Add Event listeners
document.addEventListener('DOMContentLoaded', function () {
    addEventListenerToSearch('user');
    addEventListenerToSort('user');
    addEventListenerToPage('user');

    addEventListenerToSearch('article');
    addEventListenerToSort('article');
    addEventListenerToPage('article');

    addEventListenerToSearch('comment');
    addEventListenerToSort('comment');
    addEventListenerToPage('comment');

    // Initial load
    fetchAndLoadData('user');
    fetchAndLoadData('article');
    fetchAndLoadData('comment');
});

