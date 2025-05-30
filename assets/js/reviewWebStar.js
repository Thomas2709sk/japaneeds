// Get the element by its ID or class
document.addEventListener("DOMContentLoaded", function () {
    const stars = document.querySelectorAll(".star");
    const ratingInput = document.getElementById("reviews_website_form_rate");
    const noteText = document.getElementById("note-text");

    // Highlights the stars on hover
    stars.forEach((star) => {
        star.addEventListener("mouseover", function () {
            const value = parseInt(this.getAttribute("data-value"));
            highlightStars(value);
        });
        // Reset highlights if no hover 
        star.addEventListener("mouseout", function () {
            const currentRating = parseInt(ratingInput.value) || 0;
            highlightStars(currentRating);
        });
        // Change the value of the rating when you click on the stars
        star.addEventListener("click", function () {
            const value = parseInt(this.getAttribute("data-value"));
            // Update the value of the hidden input (rate) of the form
            ratingInput.value = value; 
            noteText.textContent = `Note: ${value} sur 5`;
        });
    });

    // Add class when you click on stars to change is colors
    function highlightStars(value) {
        stars.forEach((star) => {
            const starValue = parseInt(star.getAttribute("data-value"));
            if (starValue <= value) {
                star.classList.add("text-warning");
            } else {
                star.classList.remove("text-warning");
            }
        });
    }
});