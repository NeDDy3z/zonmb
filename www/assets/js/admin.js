import {sendRequest} from "./xhr.js";
import {encodeHtml, prettyDate} from "./utils.js";
import {openOverlay} from "./overlay.js";




// Open details on article-data click
function addOpenOverlay() {
    let overlayItems = document.querySelectorAll('.overlay-item');
    overlayItems.forEach(item => {
        item.addEventListener('click', () => {
            openOverlay(item);
        });
    });
}


// Edit data button template
function createEditButton(table, param) {
    let editLink = document.createElement('a');
    editLink.href = table + '?id=' + param;
    editLink.classList.add('edit');

    let editButton = document.createElement('button');
    editButton.innerText = 'Upravit';

    editLink.appendChild(editButton);
    return editLink;
}

// Delete data button template
function createDeleteButton(table, param) {
    let deleteButton = document.createElement('button') // Create delete button
    deleteButton.classList.add('delete', 'danger');
    deleteButton.innerText = 'Smazat';
    deleteButton.addEventListener('click', function () {
        switch (table) {
            case 'users':
                if (confirm('Opravdu chcete smazat uživatele s ID: ' + param + ' ?')) {
                    sendRequest('GET', `../users/delete?id=${param}`);
                    fetchAndLoadData('users');
                }
                break;
            case 'articles':
                if (confirm('Opravdu chcete smazat článek s ID: ' + param + ' ?')) {
                    sendRequest('GET', `../articles/delete?id=${param}`);
                    fetchAndLoadData('articles');
                }
                break;
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
            let row = (table === 'users') ? userRow(dataItem) : articleRow(dataItem);

            if (dataItem.role !== 'owner') {
                let buttonDiv = row.querySelector('.buttons');
                buttonDiv.appendChild(createEditButton(`${table}/edit`, dataItem.id));
                buttonDiv.appendChild(createDeleteButton(`${table}`, dataItem.id)); // Append delete button to row
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

    let query = `${table}/get?`;
    query += (search) ? `search=${search.value}` : '';
    query += (sortField) ? `&sort=${sortField.id.split('-')[1]}` : '';
    query += (sortDirection) ? `&sortDirection=${sortDirection}` : '';
    query += (page) ? `&page=${page}` : '';

    return query;
}
// Single function to fetch and load data
function fetchAndLoadData(table) {
    sendRequest('GET', tableQuery(table), function (data) {
        loadData(table, JSON.parse(data.response));
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
            <td><a href="./articles/${article.slug}">${encodeHtml(article.id)}</a></td>
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
        button.addEventListener('click', function (e) {
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
    addEventListenerToSearch('users');
    addEventListenerToSort('users');
    addEventListenerToPage('users');

    addEventListenerToSearch('articles');
    addEventListenerToSort('articles');
    addEventListenerToPage('articles');


// Initial load
    fetchAndLoadData('users');
    fetchAndLoadData('articles');
});

