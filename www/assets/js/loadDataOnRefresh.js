function getInputFields() {
    let form = document.querySelector('form');
    let inputs = Array.from(form.getElementsByTagName('input'));
    let textareas = Array.from(form.getElementsByTagName('textarea'));

    return [...textareas, ...inputs];
}

// Save data to sessionStorage on input change
function enableSavingFormDataOnBlur() {
    let fields = getInputFields();

    fields.forEach(field => {
        if (field.name.includes('password')) return;
        field.addEventListener('blur', () => {
            
            sessionStorage.setItem(field.name, field.value);
        });
    });
}

// Load data into form
function loadFormDataOnLoad() {
    let fields = getInputFields();

    fields.forEach(field => {
        if (sessionStorage.getItem(field.name)) {
            field.value = sessionStorage.getItem(field.name);
        }
    });
}

function clearDataFromSessionStorageOnSuccess() {
    if (new URLSearchParams(window.location.search).has('success')) {
        sessionStorage.clear();
    }
}


document.addEventListener('DOMContentLoaded', clearDataFromSessionStorageOnSuccess);
document.addEventListener('DOMContentLoaded', loadFormDataOnLoad);
enableSavingFormDataOnBlur();


// I'm well aware that the fields are saved even on successful form submission. It is an intention.
