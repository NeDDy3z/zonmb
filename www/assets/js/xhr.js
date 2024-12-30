// Fetch data from server
function sendRequest(method = 'GET', query, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, query, true);

    xhr.onload = function () {
        if (typeof callback === 'function') {
            callback(xhr); // Ensure callback is a function before calling it
        }
    };
    xhr.onerror = function () {
        console.error('Request failed');
    };
    xhr.send();
}

function sendRequestWithPayload(method = 'GET', query, callback, data) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, query, true);

    xhr.onload = function () {
        callback(xhr);
    };
    xhr.send(data);
}

export {sendRequest, sendRequestWithPayload};