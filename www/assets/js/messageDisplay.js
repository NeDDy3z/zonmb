const URL_PARAMS = new URLSearchParams(window.location.search);
const staticMessageContainer = document.querySelector('.message-container.static');

// Success message dictionary
const successMessages = {
    // General success
    'login': 'Přihlášení proběhlo úspěšně',
    'register': 'Registrace proběhla úspěšně',
    'logout': 'Odhlášení proběhlo úspěšně',

    // Data uploads success
    'imageUpload': 'Obrázek byl úspěšně nahrán',
    'fullnameEdited': 'Jméno bylo úspěšně upraveno',
    'passwordChanged': 'Heslo bylo změněno',
    'userEdited': 'Profil byl úspěšně upraven',
    'userDeleted': 'Uživatel byl úspěšně smazán',
    'articleAdded': 'Článek byl úspěšně vytvořen',
    'articleEdited': 'Článek byl úspěšně upraven',
    'articleDeleted': 'Článek byl úspěšně smazán',
    'imageDelete': 'Obrázek odstraněn',
};

// Error message dictionary
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
    'alreadyLoggedIn': 'Již jste přihlášeni',


    // Article form errors - temporary
    'articleAddError': 'Článek se nepodařilo vytvořit',
    'articleEditError': 'Článek se nepodařilo upravit',
    'articleDeleteError': 'Článek se nepodařilo smazat',
    'articleNotFound': 'Článek nebyl nalezen',


    // Data errors
    'usernameEmpty': 'Vyplňte přezdívku',
    'usernameSize': 'Jméno musí mít délku minimálně 3 a maxilmálně 30 znkaů',
    'usernameRegex': 'Jméno může obsahovat pouze následující znaky: a-z A-Z 0-9 . _',
    'usernameTaken': 'Uživatelské jméno již existuje',

    'fullnameEmpty': 'Vyplňte jméno',
    'fullnameSize': 'Jméno musí mít délku minimálně 3 a maxilmálně 30 znkaů',
    'fullnameRegex': 'Jméno může obsahovat pouze písmena a mezery',

    'passwordEmpty': 'Vyplňte hesla',
    'passwordMatch': 'Hesla se neshodují',
    'passwordSize': 'Heslo musí mít délku minimálně 8 znaků',
    'passwordRegex': 'Heslo musí obsahovat alespoň jedno velké písmeno a číslici',
    'missingOldPassword': 'Vyplňte staré heslo',

    'imageUploadError': 'Obrázek se nepodařilo nahrát',
    'imageSize': 'Obrázek je příliš velký, max 2MB',
    'imageFormat': 'Nahrávejte pouze obrázky ve formátu JPG nebo PNG',
    'imageDimensions': 'Obrázek musí mít minimálně 200x200px a maximálně 4000x4000px',

    'titleEmpty': 'Vyplňte titulek',
    'titleSize': 'Titulek musí mít délku minimálně 10 a maxilmálně 100 znkaů',
    'titleTaken': 'Článek s tímto titulkem již existuje',
    'subtitleEmpty': 'Vyplňte podtitulek',
    'subtitleSize': 'Poditulek musí mít délku minimálně 3 a maxilmálně 500 znkaů',
    'contentEmpty': 'Vyplňte obsah',
    'contentSize': 'Obsah musí mít délku minimálně 3 znaků a maximálně 5000',
};


// Display message
function displayMessage(type, message, container = 'popup', countdown = 10) {
    // Convert string into array
    message = (typeof message === 'string') ? Array.from(message) : message;

    // Build messages
    let messages = message.map(msg => {
        let messageElement = document.createElement('p');
        messageElement.className = type + '-message';
        messageElement.textContent = (type === 'success') ? successMessages[msg] ?? msg : errorMessages[msg] ?? msg;

        return messageElement;
    });

    // Place message where it is supposed to be
    if (container === 'popup' && messages.length > 0) {
        document.querySelector('.message-container.popup')?.remove();

        // Create message container
        let messageContainer = document.createElement('div');
        messageContainer.classList.add('message-container');
        messageContainer.classList.add('popup');
        messageContainer.addEventListener('click', (e) => { // Hide on click
            e.preventDefault();
            messageContainer.remove();
        }, true);

        messages.forEach(msg => {
            messageContainer.appendChild(msg);
        });

        // Countdown timer
        let countdownElement = document.createElement('p');
        countdownElement.className = 'countdown';
        countdownElement.textContent = `${countdown}s`;
        messageContainer.appendChild(countdownElement);

        document.querySelector('body').appendChild(messageContainer);

        // Countdown timer
        setInterval(() => {
            countdown--;
            countdownElement.textContent = `${countdown}s`;
            if (countdown <= 0) {
                messageContainer.remove();
            }
        }, 1000);

        // Remove after 20 seconds
        setTimeout(() => {
            messageContainer.remove();
        }, countdown * 1000);
    } else if (container === 'static') {
        if (staticMessageContainer !== undefined) {
            staticMessageContainer.innerHTML = '';
            messages.forEach(msg => {
                staticMessageContainer.appendChild(msg);
            })
        }
    }
}


// Send message signal - used for messages inside URL
function sendSignalOnURLMessage() {
    let error = URL_PARAMS.get('error') ?? null;
    let success = URL_PARAMS.get('success') ?? null;

    // Construct error event
    if (error) {
        let customEvent = new CustomEvent('infoMessage', {
            detail: {
                type: 'error',
                message: error.split('-'),
                container: 'popup',
            }
        });

        window.dispatchEvent(customEvent);
    }

    // Construct success event
    if (success) {
        let customEvent = new CustomEvent('infoMessage', {
            detail: {
                type: 'success',
                message: success.split('-'),
                container: 'popup',
            }
        });

        window.dispatchEvent(customEvent);
    }
}

// Check for messages in incoming signals
window.addEventListener('infoMessage', (event) => {
    displayMessage(event.detail.type, event.detail.message, event.detail.container ?? 'popup');
});

// Check for messages inside URL
document.addEventListener('DOMContentLoaded', sendSignalOnURLMessage);


