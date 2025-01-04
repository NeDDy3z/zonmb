// Overlay
const overlay = document.querySelector('.overlay');
const overlayContent = document.querySelector('.overlay-content');
const overlayClose = document.querySelector('.overlay-close');


// Opening & closing overlay
function openOverlay(element) {
    overlay.style.display = 'block';
    overlay.querySelector('p').innerHTML = element.innerHTML;

    // Reset images
    overlay.querySelectorAll('img').forEach(img => img.remove()); // remove images from overlay

    // Display images if there are any
    if (element.classList.contains('overlay-image-item')) {
        let images = element.innerHTML.split(',');
        images.forEach(image => {
            let img = new Image();
            img.src = image;
            overlayContent.appendChild(img);
        });

        overlay.querySelector('p').innerHTML = element.innerHTML.split(',').join('<br>');
    }

    if (element.classList.contains('user')) {
        let imgs = overlay.querySelectorAll('img');

        for (let i = 1; i < imgs.length; i++) {
            imgs[i].remove();
        }
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
