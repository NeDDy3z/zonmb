const usersTable = document.querySelector('.users-table');
const articlesTable = document.querySelector('.articles-table');


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


// Inline editing
function openEdits() {
    let editable = document.querySelectorAll('.editable');
    editable.forEach(el => {
        el.setAttribute('contenteditable', 'true');
        el.style.backgroundColor = '#f9f9f9';
    });
}

// Open details on articledata click
function addOpenOverlayToArticlesTable() {
    let rows = articlesTable.querySelectorAll('tr');

    // For each row add "open overley with detailed content on click", except for the first row (header)
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

function createDeleteButton(method, param) {
    let deleteButton = document.createElement('button') // Create delete button
    deleteButton.classList.add('delete', 'danger');
    deleteButton.innerText = 'Smazat';
    deleteButton.addEventListener('click', function () {
        switch (method) {
            case 'users':
                if (confirm('Opravdu chcete smazat uživatele s ID: ' + param + ' ?')) {
                    deleteUser(param);
                }
                break;
            case 'articles':
                if (confirm('Opravdu chcete smazat článek s ID: ' + param + ' ?')) {
                    deleteArticle(param);
                }
                break;
        }
    });

    return deleteButton;
}


// Get data
function getUsers(callback) {
    const search = document.getElementById('search-user').value;
    const sortField = usersTable.querySelector('.sort.active');
    //const page = usersTable.querySelector('.pagination .active').textContent; // TODO: paging
    const page = 1;

    let query = 'users/get?';
    query += (search) ? `search=${search}` : '';
    query += (sortField) ? `&sort=${sortField}` : '';
    query += (page) ? `&page=${page}` : '';

    const xhr = new XMLHttpRequest();
    xhr.withCredentials = true;
    xhr.open('GET', query, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            document.querySelector('.users-table tbody').innerHTML = xhr.responseText;
            callback(JSON.parse(xhr.responseText));
        }
    };
    xhr.send();
}

function getArticles(callback) {
    const search = document.getElementById('search-article').value;
    const sortField = articlesTable.querySelector('.sort.active');
    //const page = articlesTable.querySelector('.pagination .active').textContent;
    const page = 1;

    let query = 'articles/get?';
    query += (search) ? `search=${search}` : '';
    query += (sortField) ? `&sort=${sortField}` : '';
    query += (page) ? `&page=${page}` : '';

    const xhr = new XMLHttpRequest();
    xhr.open('GET', query, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            document.querySelector('.articles-table tbody').innerHTML = xhr.responseText;
            callback(JSON.parse(xhr.responseText));
        }
    };
    xhr.send();
}

// Load data to table
function loadUsers(data) {
    let tbody = usersTable.querySelector('tbody');
    tbody.innerHTML = '';

    data.forEach(user => {
        let row = document.createElement('tr');
        row.innerHTML = `
            <td><a href="./users/${user.username}">${encodeHtml(user.id)}</a></td>
            <td>${encodeHtml(user.username)}</td>
            <td>${encodeHtml(user.fullname)}</td>
            <td>${encodeHtml(user.role)}</td>
            <td>${encodeHtml(prettyDate(user.created_at))}</td>
            <td class="buttons">
                <button class="edit">Upravit</button>
            </td>
        `;

        if (user.role !== 'owner') {
            row.querySelector('.buttons').appendChild(createDeleteButton('users', user.id)); // Append delete button to row
        }

        if (data.length === 0) {
            let row = document.createElement('tr');
            row.innerHTML = `
                <td colspan="6" class="empty">Žádní uživatelé nenalezeni</td>
            `;
        }

        tbody.appendChild(row);
    });
}

function loadArticles(data) {
    let tbody = articlesTable.querySelector('tbody');
    tbody.innerHTML = '';

    try {
        data.forEach(article => {
            let row = document.createElement('tr');
            row.innerHTML = `
            <td><a href="./articles/${article.slug}">${encodeHtml(article.id)}</a></td>
            <td>${encodeHtml(article.title)}</td>
            <td>${encodeHtml(article.subtitle)}</td>
            <td>${encodeHtml(article.content)}</td>
            <td>${encodeHtml(article.image_paths)}</td>
            <td>${encodeHtml(article.author_id)}</td>
            <td>${encodeHtml(prettyDate(article.created_at))}</td>
            <td class="buttons">
                <a href="articles/edit?id=${article.id}"><button>Upravit</button></a>
            </td>                
        `;

            row.querySelector('.buttons').appendChild(createDeleteButton('articles', article.id)); // Append delete button to row

            tbody.appendChild(row);
        });
    } catch (e) {
    }


    if (data.length === 0) {
        let row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="8" class="empty">Žádné články nenalezeny</td>
        `;
        tbody.appendChild(row);
    }

    addOpenOverlayToArticlesTable();
}

function deleteUser(id) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `users/delete?id=${id}`, true);
    xhr.onload = function () {
        if (xhr.status === 200 && xhr.responseText.includes('success')) {
            getUsers(function (data) {
                loadUsers(data);
            });
        }
    };
    xhr.send();
}

function deleteArticle(id) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `articles/delete?id=${id}`, true);
    xhr.onload = function () {
        if (xhr.status === 200 && xhr.responseText.includes('success')) {
            getArticles(function (data) {
                loadArticles(data);
            });
        }
    };
    xhr.send();
}

getUsers(function (data) {
    loadUsers(data);
});

getArticles(function (data) {
    loadArticles(data);
});

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




