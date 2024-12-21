// Fetch data from server
function getData(element, query, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', query, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            callback(JSON.parse(xhr.responseText));
        }
    };
    xhr.send();
}
// TODO display error

// Delete data
function deleteData(table, id, script) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `${table}/delete?id=${id}`, true);

    xhr.onload = function () {
        if (xhr.status === 200 && xhr.responseText.includes('success')) {
        }
    };
    xhr.send();
}

export { getData, deleteData };