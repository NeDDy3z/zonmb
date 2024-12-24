// Fetch data from server
function sendRequest(method = 'GET', query, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, query, true);
    xhr.withCredentials = true;

    xhr.onload = function () {
        callback(xhr);
    };
    xhr.send();
}

export {sendRequest};