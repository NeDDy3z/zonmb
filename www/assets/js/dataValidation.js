import {getData} from "./xhr.js";
// TODO FIXXXXXXXXXx


// All the regular expressions and constants used for data validation
const USERNAME_REGEX = /^[a-zA-Z0-9_.]{3,30}$/;
const USERNAME_MIN_LENGTH = 3;
const USERNAME_MAX_LENGTH = 30;

const FULLNAME_REGEX = /^[a-zA-ZáčďéěíňóřšťúůýžÁČĎÉĚÍŇÓŘŠŤÚŮÝŽ\s'-]+$/;
const FULLNAME_MIN_LENGTH = 3;
const FULLNAME_MAX_LENGTH = 30;

const PASSWORD_REGEX = /^(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,255}$/; // TODO: fix all regexes
const PASSWORD_MIN_LENGTH = 8;
const PASSWORD_MAX_LENGTH = 255;

const TITLE_MIN_LENGTH = 3;
const TITLE_MAX_LENGTH = 100;

const SUBTITLE_MIN_LENGTH = 3;
const SUBTITLE_MAX_LENGTH = 1000;

const CONTENT_MIN_LENGTH = 3;
const CONTENT_MAX_LENGTH = 5000;

const IMG_SIZE = 1000000;
const IMG_PX = 500;
const IMG_TYPES = ['image/jpeg', 'image/png'];

// Page form
const form = document.querySelector('form') ?? null;


// Send message signal
function sendErrorSignal(error) {
    let cont = (!window.location.href.includes('/users') || !window.location.href.includes('/login')) ? 'static' : 'popup';
    const event = new CustomEvent('message', {
        detail: {
            type: 'error',
            message: error,
            container: cont,
        }
    });
    window.dispatchEvent(event);
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
            3
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
            validateImage(value);
            break;
        default:
            return {valid: true, message: ''}; // Default for unhandled input types
            break;
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
    getData('users/exists?username=' + username, function (data) {
        if (data.exists) {
            error.push('usernameTaken');
        }
    });

    sendErrorSignal(error);
}

function validateFullname(fullname) {
    const error = [];

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
    const error = [];

    switch (true) {
        case password === null || passwordConfirm === null || password === '' || passwordConfirm === '': // Empty
            error.push('passwordEmpty');
            break;

        case password.length < PASSWORD_MIN_LENGTH || password.length > PASSWORD_MAX_LENGTH: // Length
            error.push('passwordSize');

        case password !== passwordConfirm: // Matching passwords
            error.push('passwordMatch');

        case PASSWORD_REGEX.test(password): // Regex
            error.push('passwordRegex');
            break;
    }

    sendErrorSignal(error);
}

function validateTitle(title) {
    const error = [];

    switch (true) {
        case title === null || title === '': // Empty
            error.push('titleEmpty');
            break;

        case title.length < TITLE_MIN_LENGTH || title.length > TITLE_MAX_LENGTH: // Length
            error.push('titleSize');

    }

    // Check if title exists
    getData('articles/exists?title=' + title, function (data) {
        if (data.exists) {
            error.push('titleExists');
        }
    });

    sendErrorSignal(error);
}

function validateSubtitle(subtitle) {
    const error = [];

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
    const error = [];

    switch (true) {
        case content === null || content === '': // Empty
            error.push('contentEmpty');
            break;

        case content.length < CONTENT_MIN_LENGTH || content.length > CONTENT_MAX_LENGTH: // Length
            error.push('contentSize');
    }

    sendErrorSignal(error);
}

function validateImage(image) {
    const {
        size = 2_000_000, // Default max size in bytes (2MB)
        minWidth = 200,
        minHeight = 200,
        maxWidth = 4000,
        maxHeight = 4000
    } = options;

    // Check if the image exists and a file was uploaded
    if (!image || !image.tmp_name) {
        throw new Error('uploadError');
    }

    const error = [];

    // Validate the image size
    if (image.size > size) {
        error.push('imageSize');
    }

    // Validate the image dimensions
    const img = new Image();
    img.src = URL.createObjectURL(image); // Create a temporary URL for the uploaded image

    img.onload = () => {
        const {width, height} = img;

        if (
            width < minWidth ||
            width > maxWidth ||
            height < minHeight ||
            height > maxHeight
        ) {
            error.push('imageDimensions');
        }

        // If there are any errors, throw them
        if (error.length > 0) {
            sendErrorSignal(error);
        }

        // If all validation passes
        return true;
    };

    img.onerror = () => {
        sendErrorSignal(error);
    };
}


// Add event listeners
function addEventListenerToFormInputs(form) {
    if (!form) {
        return;
    }

    const inputs = form.querySelectorAll('input');

    inputs.forEach(input => {
        input.addEventListener('blur', function () { // On unfocus
            validate(input.name, input.value);
        });

        input.addEventListener('input', function (e) { // Wait one second
            clearTimeout(this.timer);

            this.timer = setTimeout(() => {
                validate(input.name, input.value);
            }, 1000);
        });
    });


}

addEventListenerToFormInputs(form);
