// Image slider
document.addEventListener("DOMContentLoaded", function () {
    const slides = document.querySelectorAll(".slide");
    const prevButton = document.querySelector(".prev-button");
    const nextButton = document.querySelector(".next-button");

    let currentSlide = 0;
    const slideCount = slides.length;

    // Function to show a specific slide
    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.toggle("active", i === index);
        });
        currentSlide = index;
    }

    // Show the next slide
    function nextSlide() {
        const nextIndex = (currentSlide + 1) % slideCount;
        showSlide(nextIndex);
    }

    // Show the previous slide
    function prevSlide() {
        const prevIndex = (currentSlide - 1 + slideCount) % slideCount;
        showSlide(prevIndex);
    }

    // Add event listeners for navigation buttons
    nextButton.addEventListener("click", () => {
        nextSlide();
        resetAutoSlide();
    });

    prevButton.addEventListener("click", () => {
        prevSlide();
        resetAutoSlide();
    });

    // Reset the auto-slide interval when manually navigating
    function resetAutoSlide() {
        clearInterval(autoSlideInterval);
        autoSlideInterval = setInterval(nextSlide, 5000);
    }
});