const tables = document.querySelectorAll('.articles-table');
const overlay = document.querySelector('.overlay');
const overlayContent = document.querySelector('.overlay-content');
const overlayClose = document.querySelector('.overlay-close');


// Opening & closing overlay
function openOverlay(header, content) {
    overlay.style.display = 'block';
    overlay.querySelector('h1').textContent = header;
    overlay.querySelector('p').innerHTML = content;

    // Display images if there are any
    if (header.includes('Obrázky')) {
        let images = content.split(',');
        images.forEach(image => {
            let img = new Image();
            img.src = image;
            overlayContent.appendChild(img);
        });

        overlay.querySelector('p').innerHTML = content.split(',').join('<br>');
    }
}

function closeOverlay() {
    overlay.style.display = 'none';
    overlay.querySelectorAll('img').forEach(img => img.remove()); // remove images from overlay
}


// Inline editing
function openEdits() {
    let editable = document.querySelectorAll('.editable');
    editable.forEach(el => {
        el.setAttribute('contenteditable', 'true');
        el.style.backgroundColor = '#f9f9f9';
    });
}


// Event listeners
overlayClose.addEventListener('click', closeOverlay);
overlay.addEventListener('click', closeOverlay); // Close overlay on click outside of it
overlayContent.addEventListener('click', (e) => e.stopPropagation()); // Prevent close on click inside overlay (e.g. on content/text for copying...)

tables.forEach(table => {
    // Get all rows
    let rows = table.querySelectorAll('tr');

    // For each row add "open overley with detailed content on click", except for the first row (header)
    for (let i = 1; i < rows.length; i++) {
        for (let j = 0; j < rows[i].children.length; j++) {
            rows[i].children[j].addEventListener('click', () => {
                openOverlay(rows[0].children[j].textContent, rows[i].children[j].textContent);
            });
        }
    }
});
