document.addEventListener("DOMContentLoaded", function () {
    const stars = document.querySelectorAll(".star");
    const ratingInput = document.getElementById("reviews_guide_form_rate"); // Champ caché généré par Symfony
    const noteText = document.getElementById("note-text");

    stars.forEach((star) => {
        star.addEventListener("mouseover", function () {
            const value = parseInt(this.getAttribute("data-value"));
            highlightStars(value);
        });

        star.addEventListener("mouseout", function () {
            const currentRating = parseInt(ratingInput.value) || 0;
            highlightStars(currentRating);
        });

        star.addEventListener("click", function () {
            const value = parseInt(this.getAttribute("data-value"));
            ratingInput.value = value; // Met à jour la valeur du champ caché
            noteText.textContent = `Note: ${value} sur 5`;
        });
    });

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