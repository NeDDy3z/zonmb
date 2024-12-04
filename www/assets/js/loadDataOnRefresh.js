let form = document.querySelector('form');
let fields = form.getElementsByTagName('input');


// Save data to localStorage on input change
function enableSavingFormDataOnBlur() {
    for (let i = 0; i < fields.length; i++) {
        if (fields[i].name.includes('password')) continue;
        fields[i].addEventListener('blur', () => {
            localStorage.setItem(fields[i].name, fields[i].value);
        });
    }
}

// Load data into form
function loadFormDataOnLoad() {
    for (let i = 0; i < fields.length; i++) {
        if (localStorage.getItem(fields[i].name)) {
            fields[i].value = localStorage.getItem(fields[i].name);
        }
    }
}

function clearDataFromLocalStorageOnSuccess() {
    if (new URLSearchParams(window.location.search).has('success')) {
        localStorage.clear();
    }
}


document.addEventListener('load', loadFormDataOnLoad);
document.addEventListener('load', clearDataFromLocalStorageOnSuccess);
enableSavingFormDataOnBlur();


// I'm well aware that the fields are saved even on successful form submission. It is an intention.
