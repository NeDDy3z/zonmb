import {sendRequest} from "./xhr.js";
import {sendMessageSignal} from "./messageDisplay";
// All the regular expressions and constants used for data validation
const USERNAME_REGEX = /^[a-zA-Z0-9_.]{3,30}$/;
const USERNAME_MIN_LENGTH = 3;
const USERNAME_MAX_LENGTH = 30;

const FULLNAME_REGEX = /^[a-zA-ZáčďéěíňóřšťúůýžÁČĎÉĚÍŇÓŘŠŤÚŮÝŽ\s'-]+$/;
const FULLNAME_MIN_LENGTH = 3;
const FULLNAME_MAX_LENGTH = 30;

const PASSWORD_REGEX = /^(?=.*[A-Z])(?=.*\d).+$/;
const PASSWORD_MIN_LENGTH = 8;
const PASSWORD_MAX_LENGTH = 255;

const TITLE_MIN_LENGTH = 10;
const TITLE_MAX_LENGTH = 100;

const SUBTITLE_MIN_LENGTH = 3;
const SUBTITLE_MAX_LENGTH = 255;

const CONTENT_MIN_LENGTH = 3;
const CONTENT_MAX_LENGTH = 5000;

const IMG_SIZE = 2_000_000;
const IMG_MIN_PX = 200;
const IMG_MAX_PX = 4000;
const IMG_TYPES = ['image/jpeg', 'image/png'];


// Send message signal
function sendErrorSignal(error) {
    let href = window.location.href;

    if (href.includes('/login')) {
        return;
    }

    sendMessageSignal(
        'error',
        error,
        (href.includes('/users')) ? 'popup' : 'static',
    );
}


// Route validation
function validate(type, value) {
    switch (type) { // no breaks are intentional
        case 'username':
            validateUsername(value);
            break;
        case 'fullname':
            validateFullname(value);
            break;
        case 'password':
            validatePasswords(value, document.querySelector('input[name="password-confirm"]')?.value);
            break;
        case 'password-confirm':
            validatePasswords(document.querySelector('input[name="password"]')?.value, value);
            break;
        case 'password-old':
            validatePasswords(value, value);
            break;
        case 'title':
            validateTitle(value);
            break;
        case 'subtitle':
            validateSubtitle(value);
            break;
        case 'content':
            validateContent(value);
            break;
        case 'image':
        case 'image[]':
            validateImage(value);
            break;
        default:
            return {valid: true, message: ''}; // Default for unhandled input types
    }
}

function validateUsername(username) {
    let error = [];
    switch (true) {
        case username == null || username === '': // Empty
            error.push('usernameEmpty');
            break;

        case username.length < USERNAME_MIN_LENGTH || username.length > USERNAME_MAX_LENGTH: // Length
            error.push('usernameSize');

        case !USERNAME_REGEX.test(username): // Regex
            error.push('usernameRegex');
    }

    // Check if username is taken
    sendRequest('GET', 'users/exists?username=' + encodeURIComponent(username), function (data) {
        data = JSON.parse(data.response);
        if (data.exists) {
            sendErrorSignal(['usernameTaken']);
        } else if (data.error) {
            sendErrorSignal(data.error);
        }
    });

    sendErrorSignal(error);
}

function validateFullname(fullname) {
    let error = [];

    switch (true) {
        case fullname === null || fullname === '': // Empty
            error.push('fullnameEmpty');
            break;

        case fullname.length < FULLNAME_MIN_LENGTH || fullname.length > FULLNAME_MAX_LENGTH: // Length
            error.push('fullnameSize');

        case !FULLNAME_REGEX.test(fullname): // Regex
            error.push('fullnameRegex');
            break;
    }

    sendErrorSignal(error);
}

function validatePasswords(password, passwordConfirm) {
    let error = [];

    switch (true) {
        case password === null || passwordConfirm === null || password === '' || passwordConfirm === '': // Empty
            error.push('passwordEmpty');
            break;

        case password.length < PASSWORD_MIN_LENGTH || password.length > PASSWORD_MAX_LENGTH: // Length
            error.push('passwordSize');

        case password !== passwordConfirm: // Matching passwords
            error.push('passwordMatch');

        case !PASSWORD_REGEX.test(password): // Regex
            error.push('passwordRegex');
            break;
    }

    sendErrorSignal(error);
}

function validateTitle(title) {
    let error = [];

    switch (true) {
        case title === null || title === '': // Empty
            error.push('titleEmpty');
            break;

        case title.length < TITLE_MIN_LENGTH || title.length > TITLE_MAX_LENGTH: // Length
            error.push('titleSize');

    }

    // Check if title is taken
    sendRequest('GET', 'exists?title=' + encodeURIComponent(title), function (data) {
        data = JSON.parse(data.response);
        if (data.exists) {
            sendErrorSignal(['titleTaken']);
        } else if (data.error) {
            sendErrorSignal(data.error);
        }
    });

    sendErrorSignal(error);
}

function validateSubtitle(subtitle) {
    let error = [];

    switch (true) {
        case subtitle === null || subtitle === '': // Empty
            error.push('subtitleEmpty');
            break;

        case subtitle.length < SUBTITLE_MIN_LENGTH || subtitle.length > SUBTITLE_MAX_LENGTH: // Length
            error.push('subtitleSize');
    }

    sendErrorSignal(error);
}

function validateContent(content) {
    let error = [];

    switch (true) {
        case content === null || content === '': // Empty
            error.push('contentEmpty');
            break;

        case content.length < CONTENT_MIN_LENGTH || content.length > CONTENT_MAX_LENGTH: // Length
            error.push('contentSize');
    }

    sendErrorSignal(error);
}

function validateImage(images) {
    let error = [];

    Array.from(images).forEach(file => {
        if (file.type.startsWith('image/')) { // Ensure the file is an image
            let reader = new FileReader();

            reader.onload = function (event) {
                const img = new Image();
                img.src = event.target.result; // Base64 image source

                img.onload = function () {
                    switch (true) {
                        case !IMG_TYPES.includes(file.type): // jpeg or png
                            error.push('imageFormat');
                            break;

                        case file.size > IMG_SIZE: // Size
                            error.push('imageSize');
                            break;

                        case img.width < IMG_MIN_PX || img.width > IMG_MAX_PX ||
                        img.height < IMG_MIN_PX || img.height > IMG_MAX_PX: // Dimension
                            error.push('imageDimensions');
                            break;
                    }

                    sendErrorSignal(error);
                };
            };
            reader.readAsDataURL(file); // Convert image file to Base64
        }
    });
}


// Add event listeners
function addEventListenerToFormInputs(form) {
    if (!form) {
        return;
    }

    const inputs = [...form.querySelectorAll('input'), ...form.querySelectorAll('textarea')];

    inputs.forEach(input => {
        input.addEventListener('blur', function () { // On unfocus
            let value = (input.type === 'file') ? input.files : input.value;
            validate(input.name, value);
        });

        input.addEventListener('input', function () { // Wait one second
            clearTimeout(this.timer);

            this.timer = setTimeout(() => {
                let value = (input.type === 'file') ? input.files : input.value;
                validate(input.name, value);
            }, 1000);
        });
    });


}

// Page form
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form') ?? null;

    forms.forEach(form => {
        addEventListenerToFormInputs(form);
    });
});
