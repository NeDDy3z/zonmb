const URL_PARAMS = new URLSearchParams(window.location.search);
const successContainer = document.querySelector('.success-container');
const errorContainer = document.querySelector(' .error-container');

const successMessages = {
    // General success
    'login': 'Přihlášení proběhlo úspěšně',
    'register': 'Registrace proběhla úspěšně',

    // Data uploads success
    'imageUpload': 'Obrázek byl úspěšně nahrán',
    'usernameUpdate': 'Profil byl úspěšně upraven',
    'articleCreateSuccess': 'Článek byl úspěšně vytvořen',
    'articleUpdateSuccess': 'Článek byl úspěšně upraven',
};

const errorMessages = {
    // General errors
    'emptyValues': 'Vyplňte všechna povinná pole',
    'invalidValues': 'Vyplňte správně všechna pole',
    'invalidImageFormat': 'Nahrávejte pouze obrázky ve formátu JPG nebo PNG',
    'invalidImageSize': 'Obrázek je příliš velký, max 1MB',
    'loginError': 'Nesprávné jméno nebo heslo', // Login error
    'registerError': 'Registrace se nezdařila', // Registar error
    'updateError': 'Profil se nepodařilo upravit', // Profile update error
    'notAuthorized': 'K tomuto obsahu nemáte povolený přístup', // Not authorized error

    // Article form errors - temporary
    'articleCreateError': 'Článek se nepodařilo vytvořit',
    'articleUpdateError': 'Článek se nepodařilo upravit',
    'articleDeleteError': 'Článek se nepodařilo smazat',

    // Data errors
    'usernameEmpty': 'Vyplňte uživatelské jméno',
    'usernameSize': 'Jméno musí mít délku minimálně 3 a maxilmálně 30 znkaů',
    'usernameRegex': 'Jméno může obsahovat pouze následující znaky: a-z A-Z 0-9 . _',
    'usernameTaken': 'Uživatelské jméno již existuje',
    'passwordEmpty': 'Vyplňte heslo',
    'passwordMatch': 'Hesla se neshodují',
    'passwordSize': 'Heslo musí mít délku minimálně 8 znaků',
    'passwordRegex': 'Heslo musí bsahovat alespoň jedno velké písmeno a číslici',
    'imageUploadError': 'Obrázek se nepodařilo nahrát',
    'imageSize': 'Obrázek je příliš velký, max 1MB',
    'imageFormat': 'Nahrávejte pouze obrázky ve formátu JPG nebo PNG',
    'imageDimensions': 'Obrázek musí mít minimálně 100x100px a maximálně 500x500px',
    'titleEmpty': 'Vyplňte titulek',
    'titleSize': 'Titulek musí mít délku minimálně 3 a maxilmálně 100 znkaů',
    'contentEmpty': 'Vyplňte obsah',
    'contentSize': 'Obsah musí mít délku minimálně 10 znaků',
};

// Show alert window popup with message
if (URL_PARAMS.has('success')) {
    let value = URL_PARAMS.get('success');

    let message = document.createElement('p')
    message.id = value;
    message.className = 'success-message';
    message.textContent = successMessages[value];

    successContainer.appendChild(message);
}

// Show form-error messages
if (URL_PARAMS.has('error')) {
    let values = URL_PARAMS.get('error').split('-');

    values.forEach(value => {
        let message = document.createElement('p')
        message.id = values[0];
        message.className = 'error-message';
        message.textContent = errorMessages[value];
        if (errorMessages[value] === 'undefined') {
            message.textContent = value;
        }

        errorContainer.appendChild(message);
    });
}