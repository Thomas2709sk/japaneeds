//  cache autocomplete results and avoid redundant API calls
const cache = {};

//  Prevent sending a request on every keydown
function debounce(func, delay) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

// Removes suggestion elements from the autocomplete container
function clearSuggestions(container) {
    while (container.firstChild) {
        container.removeChild(container.firstChild);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const addressInput = document.getElementById('add_reservation_form_address');
    const suggestionsContainer = document.getElementById('autocomplete-results');

    if (!addressInput || !suggestionsContainer) {
        console.error("Élément manquant : adresse ou conteneur.");
        return;
    }

    // Asynchronously fetch address suggestions from OpenStreetMap's Nominatim API
    async function fetchAddressSuggestions(query) {
        if (cache[query]) {
            return cache[query];
        }

        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&addressdetails=1&limit=5`, {
            headers: {
                'User-Agent': 'Japaneeds (contact@japaneeds.com)'
            },
        });
        const data = await response.json();
        // Store results in cache
        cache[query] = data;
        return data;
    }

    // Handles user input in the address field with debounce to limit API calls
    const handleInput = debounce(async () => {
        const query = addressInput.value.trim();
        // triggers a search if the query has at least 3 characters
        if (query.length < 3) {
            clearSuggestions(suggestionsContainer);
            return;
        }

        //  display address suggestions
        const suggestions = await fetchAddressSuggestions(query);

        clearSuggestions(suggestionsContainer);

        suggestions.forEach((suggestion) => {
            const suggestionElement = document.createElement('div');
            suggestionElement.classList.add('autocomplete-suggestion', 'list-group-item', 'list-group-item-action');
            suggestionElement.textContent = suggestion.display_name;

            // When a suggestion is clicked, fill the input and clear the suggestions
            suggestionElement.addEventListener('click', () => {
                addressInput.value = suggestion.display_name;
                clearSuggestions(suggestionsContainer);
            });

            suggestionsContainer.appendChild(suggestionElement);
        });
    }, 600);

    // Close suggestions if the user clicks outside the input or suggestion list
    addressInput.addEventListener('input', handleInput);
    document.addEventListener('click', (e) => {
        if (!suggestionsContainer.contains(e.target) && e.target !== addressInput) {
            clearSuggestions(suggestionsContainer);
        }
    });
});