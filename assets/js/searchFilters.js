// not used for the moment , need to change search filters on results reservations page

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('filters-form');
    const resultsContainer = document.getElementById('results');

    form.addEventListener('change', function () {
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'GET', 
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            resultsContainer.innerHTML = html;
        })
        .catch(error => {
            console.error('Erreur lors de la mise à jour des résultats:', error);
        });
    });
});