// JS version of htmlspecialchars
function encodeHtml(str) {
    const element = document.createElement("p");
    element.innerText = str; // Encode special characters
    return element.innerHTML;
}


// Convert date to pretty date
function prettyDate(date) {
    let newDate = (new Date(date).toLocaleDateString('cs-CZ')).toString(); // Create czech date from date
    return newDate.replace(/ /g, ''); // Remove spaces
}


export {encodeHtml, prettyDate};
