const URL_PARAMS = new URLSearchParams(window.location.search);
const successContainer = document.querySelector('.success-container');
const errorContainer = document.querySelector(' .error-container');

const successMessages = {
    // General success
    'login': 'Přihlášení proběhlo úspěšně',
    'register': 'Registrace proběhla úspěšně',
    'logout': 'Odhlášení proběhlo úspěšně',

    // Data uploads success
    'imageUpload': 'Obrázek byl úspěšně nahrán',
    'fullnameEdited': 'Jméno bylo úspěšně upraveno',
    'userEdited': 'Profil byl úspěšně upraven',
    'userDeleted': 'Uživatel byl úspěšně smazán',
    'articleAdded': 'Článek byl úspěšně vytvořen',
    'articleEdited': 'Článek byl úspěšně upraven',
    'articleDeleted': 'Článek byl úspěšně smazán',
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
    'articleAddError': 'Článek se nepodařilo vytvořit',
    'articleEditError': 'Článek se nepodařilo upravit',
    'articleDeleteError': 'Článek se nepodařilo smazat',
    'articleNotFound': 'Článek nebyl nalezen',

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

function displayMessage(type) {
    // Get messages from URL
    let errors = URL_PARAMS.get('error') ?? null;
    let success = URL_PARAMS.get('success') ?? null;

    // Create message container
    let messageContainer = document.createElement('div');
    messageContainer.className = 'message-container';
    messageContainer.addEventListener('click', () => { // remove on click
        messageContainer.remove();
    }, true);

    if (!errors && !success) {
        return;
    }

    // Append messages to container
    if (errors) {
        errors.split('-').forEach(message => {
            let messageElement = document.createElement('p');
            messageElement.className = 'error-message';
            messageElement.textContent = errorMessages[message];
            messageContainer.appendChild(messageElement);
        });
    }
    if (success) {
        success.split('-').forEach(message => {
            let messageElement = document.createElement('p');
            messageElement.className = 'success-message';
            messageElement.textContent = successMessages[message];
            messageContainer.appendChild(messageElement);
        });
    }

    // Countdown timer
    let countdown = 10;
    let countdownElement = document.createElement('p');
    countdownElement.className = 'countdown';
    countdownElement.textContent = `Dvojklik pro skrýtí zprávy / ${countdown}s`;

    messageContainer.appendChild(countdownElement);

    setInterval(() => {
        countdown--;
        countdownElement.textContent = `Dvojklik pro skrýtí zprávy / ${countdown}s`;
        if (countdown <= 0) {
            messageContainer.remove();
        }
    }, 1000);

    document.querySelector('header').appendChild(messageContainer);

    // Remove after 20 seconds
    setTimeout(() => {
        messageContainer.remove();
    }, countdown * 1000);
}

displayMessage();

document.addEventListener('DOMContentLoaded', () => {
    displayMessage();
});
