// Overlay
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


// Event listeners
overlayClose.addEventListener('click', closeOverlay);
overlay.addEventListener('click', closeOverlay); // Close overlay on click outside of it
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeOverlay();
}); // Close overlay on ESC key
overlayContent.addEventListener('click', (e) => e.stopPropagation()); // Prevent close on click inside overlay (e.g. on content/text for copying...)


export {openOverlay};
