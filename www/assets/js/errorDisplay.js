const URL_PARAMS = new URLSearchParams(window.location.search);
const successContainer = document.querySelector('.success-container');
const errorContainer = document.querySelector(' .error-container');

const successMessages = {
    'loginSuccess': 'Přihlášení proběhlo úspěšně',
    'registerSuccess': 'Registrace proběhla úspěšně',

    'imageSuccess': 'Obrázek byl úspěšně nahrán',
    'profileUpdateSuccess': 'Profil byl úspěšně upraven',
    'articleCreateSuccess': 'Článek byl úspěšně vytvořen',
    'articleUpdateSuccess': 'Článek byl úspěšně upraven',
};

const errorMessages = {
    // General errors
    'emptyValues': 'Vyplňte všechna povinná pole',
    'invalidValues': 'Vyplňte správně všechna pole',
    'invalidImageFormat': 'Nahrávejte pouze obrázky ve formátu JPG nebo PNG',
    'invalidImageSize': 'Obrázek je příliš velký, max 1MB',

    // Login form errors
    'loginError': 'Nesprávné jméno nebo heslo',
    'loginInvalidUsername': 'Uživatelské jméno neexistuje',
    'loginInvalidPassword': 'Nesprávné heslo',

    // Register form errors
    'registerError': 'Registrace se nezdařila',
    'registerInvalidUsernameRegex': 'Jméno může obsahovat pouze následující znaky: a-z A-Z 0-9 . _',
    'registerInvalidUsernameSize': 'Jméno musí mít délku minimálně 3 a maxilmálně 30 znkaů',
    'registerUsernameExists': 'Uživatelské jméno již existuje',
    'registerInvalidPasswordRegex': 'Heslo musí bsahovat alespoň jedno velké písmeno a číslici',
    'registerInvalidPasswordSize': 'Heslo musí mít délku minimálně 8 znaků',

    // Profile form errors
    'profileUpdateError': 'Profil se nepodařilo upravit',

    // Article form errors - temporary
    'articleCreateError': 'Článek se nepodařilo vytvořit',
    'articleUpdateError': 'Článek se nepodařilo upravit',
    'articleDeleteError': 'Článek se nepodařilo smazat',
};


// Show alert window popup with message
if (URL_PARAMS.has('popup')) {
    let value = URL_PARAMS.get('popup');
    alert(value);
}

// Show success message
if (URL_PARAMS.has('success')) {
    let value = URL_PARAMS.get('success');

    let message = document.createElement('p')
    message.id = value;
    message.className = 'success-message';
    message.textContent = successMessages[value];

    successContainer.appendChild(message);
}

// Show error messages
if (URL_PARAMS.has('error')) {
    let values = URL_PARAMS.get('error').split('-');

    let message = document.createElement('p')
    message.id = value;
    message.className = 'error-message';
    message.textContent = errorMessages[value];

    errorContainer.appendChild(message);
}