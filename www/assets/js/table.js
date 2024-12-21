// Version of html special chars
function encodeHtml(str) {
    const element = document.createElement("p");
    element.innerText = str; // Encode special characters
    return element.innerHTML;
}

function prettyDate(date) {
    let newDate = (new Date(date).toLocaleDateString('cs-CZ')).toString(); // Create czech date from date
    return newDate.replace(/ /g, ''); // Remove spaces
}

// Open details on article-data click
function addOpenOverlayToArticlesTable() {
    let rows = document.querySelector('.table-articles').querySelectorAll('tr');

    // For each row add "open overley with detailed content on click", except for the first row (header) & ID & buttons
    for (let i = 1; i < rows.length; i++) {
        for (let j = 1; j < rows[i].children.length; j++) {
            let row = rows[i].children[j];

            if (row.querySelectorAll('button').length === 0 && row.querySelectorAll('a').length === 0) {
                row.addEventListener('click', () => {
                    openOverlay(rows[0].children[j].textContent, rows[i].children[j].innerHTML);
                });
            }
        }
    }
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
                    deleteData('users', param);
                }
                break;
            case 'articles':
                if (confirm('Opravdu chcete smazat článek s ID: ' + param + ' ?')) {
                    deleteData('articles', param);
                }
                break;
        }
    });

    return deleteButton;
}


// Fetch data from server
function getData(table, callback) {
    const tableSection = document.querySelector(`.table-${table}`);
    const search = tableSection.querySelector('.search');
    const sortField = tableSection.querySelector('.sort.active');
    const sortDirection = (sortField.classList.contains('asc')) ? 'asc' : 'desc';
    const page = tableSection.querySelector(`#page-${table}`).querySelector('span').textContent ?? 1;

    let query = `${table}/get?`;
    query += (search) ? `search=${search.value}` : '';
    query += (sortField) ? `&sort=${sortField.id}` : '';
    query += (sortDirection) ? `&sortDirection=${sortDirection}` : '';
    query += (page) ? `&page=${page}` : '';

    const xhr = new XMLHttpRequest();
    xhr.open('GET', query, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            tableSection.querySelector('tbody').innerHTML = xhr.responseText;
            callback(JSON.parse(xhr.responseText));
        }
    };
    xhr.send();
}

// Delete data
function deleteData(table, id) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `${table}/delete?id=${id}`, true);

    xhr.onload = function () {
        if (xhr.status === 200 && xhr.responseText.includes('success')) {
            fetchAndLoadData(table);
        }
    };
    xhr.send();
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
        addOpenOverlayToArticlesTable();
    }

    if (tbody.innerText === '') {
        let row = document.createElement('tr');
        row.innerHTML = `<td colspan="8" class="empty">Žádná data nenalezena</td>`;
        tbody.appendChild(row);
    }
}

// Single function to fetch and load data
function fetchAndLoadData(table) {
    getData(table, function (data) {
        loadData(table, data);
    });
}


// Table rows
function userRow(user) {
    let row = document.createElement('tr');
    row.innerHTML = `
            <td><a href="./users/${user.username}">${encodeHtml(user.id)}</a></td>
            <td>${encodeHtml(user.username)}</td>
            <td>${encodeHtml(user.fullname)}</td>
            <td>${encodeHtml(user.role)}</td>
            <td>${encodeHtml(prettyDate(user.created_at))}</td>
            <td class="buttons"></td>`;

    return row;
}

function articleRow(article) {
    let row = document.createElement('tr');
    row.innerHTML = `
            <td><a href="./articles/${article.slug}">${encodeHtml(article.id)}</a></td>
            <td>${encodeHtml(article.title)}</td>
            <td>${encodeHtml(article.subtitle)}</td>
            <td>${encodeHtml(article.content)}</td>
            <td>${encodeHtml(article.image_paths)}</td>
            <td>${encodeHtml(article.author_id)}</td>
            <td>${encodeHtml(prettyDate(article.created_at))}</td>
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
    search.addEventListener('input', function () {
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
addEventListenerToSearch('users');
addEventListenerToSort('users');
addEventListenerToPage('users');
addEventListenerToSearch('articles');
addEventListenerToSort('articles');
addEventListenerToPage('articles');

// Initial load
fetchAndLoadData('users');
fetchAndLoadData('articles');




// // Search
// document.getElementById('search-user').addEventListener('input', function () {
//     const query = this.value;
//     const xhr = new XMLHttpRequest();
//     xhr.open('GET', `search/users?parameter=${query}`, true);
//     xhr.onload = function () {
//         if (xhr.status === 200) {
//             document.querySelector('.users-table tbody').innerHTML = xhr.responseText;
//         }
//     };
//     xhr.send();
// });
//
// // Sorting
// document.querySelectorAll('.sort').forEach(header => {
//     header.addEventListener('click', function (e) {
//         e.preventDefault();
//         const sortField = this.dataset.sort;
//         const xhr = new XMLHttpRequest();
//         xhr.open('GET', `search/users?sort=${sortField}`, true);
//         xhr.onload = function () {
//             if (xhr.status === 200) {
//                 document.querySelector('.users-table tbody').innerHTML = xhr.responseText;
//             }
//         };
//         xhr.send();
//     });
// });






