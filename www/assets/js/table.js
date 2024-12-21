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

function createEditButton(table, param) {
    let editLink = document.createElement('a');
    editLink.href = table + '?id=' + param;
    editLink.classList.add('edit');

    let editButton = document.createElement('button');
    editButton.innerText = 'Upravit';

    editLink.appendChild(editButton);
    return editLink;
}

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
    const page = 1;

    let query = `${table}/get?`;
    query += (search) ? `search=${search.value}` : '';
    query += (sortField) ? `&sort=${sortField.data - sort}` : '';
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
            getData(table, function (data) {
                loadData(table, data);
            });
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

            if (data.length === 0) {
                let row = document.createElement('tr');
                row.innerHTML = `
                <td colspan="6" class="empty">Žádná data nenalezena</td>
            `;
            }

            tbody.appendChild(row);
        });
        addOpenOverlayToArticlesTable();
    }
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


// Event listeners
// document.querySelectorAll('.table-articles .sort').forEach(header => {
//     header.addEventListener('click', function (e) {
//         e.preventDefault();
//         const sortField = this.dataset.sort;
//         const xhr = new XMLHttpRequest();
//         xhr.open('GET', `articles/get?sort=${sortField}`, true);
//         xhr.onload = function () {
//             if (xhr.status === 200) {
//                 document.querySelector('.table-articles tbody').innerHTML = xhr.responseText;
//             }
//         }
//     })
// })

document.querySelector('.table-users .search').addEventListener('input', function () {
    getData('users', function (data) {
        loadData('users', data);
    })
})

document.querySelector('.table-articles .search').addEventListener('input', function () {
    getData('articles', function (data) {
        loadData('articles', data);
    })
})


// Initial load
getData('users', function (data) {
    loadData('users', data);
});

getData('articles', function (data) {
    loadData('articles', data);
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




