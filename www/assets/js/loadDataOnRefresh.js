const inputs = [
    ...Array.from(document.querySelectorAll('input')),
    ...Array.from(document.querySelectorAll('textarea'))
];

// Save data to sessionStorage on input change
function enableSavingData() {
    // Enable saving on blur or change input
    inputs.forEach(field => {
        if (field.name.includes('password')) return;
        if (field.value !== '') {
            sessionStorage.setItem(field.name, field.value);
        }

        field.addEventListener('input', () => {
            sessionStorage.setItem(field.name, field.value);
        });
        field.addEventListener('blur', () => {
            sessionStorage.setItem(field.name, field.value);
        });
    });
}

// Load data into form
function loadFormDataOnLoad() {
    if (inputs.length === 0) return;
    inputs.forEach(field => {
        let val = sessionStorage.getItem(field.name)
        if (!field.name.includes('image') && val && window.location.href.includes('error')) {
            field.value = sessionStorage.getItem(field.name);
        }
    });
}

// Clear data
function clearDataFromSessionStorageOnSuccess() {
    if (new URLSearchParams(window.location.search).has('success')) {
        sessionStorage.clear();
    }
}


document.addEventListener('DOMContentLoaded', loadFormDataOnLoad);
document.addEventListener('DOMContentLoaded', enableSavingData);
document.addEventListener('DOMContentLoaded', clearDataFromSessionStorageOnSuccess);


// I'm well aware that the fields are saved even on successful form submission. It is an intention.
