const cache = {};

function debounce(func, delay) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

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
        cache[query] = data; // Stocke les résultats dans le cache
        return data;
    }

    const handleInput = debounce(async () => {
        const query = addressInput.value.trim();
        if (query.length < 3) {
            clearSuggestions(suggestionsContainer);
            return;
        }

        const suggestions = await fetchAddressSuggestions(query);

        clearSuggestions(suggestionsContainer);

        suggestions.forEach((suggestion) => {
            const suggestionElement = document.createElement('div');
            suggestionElement.classList.add('autocomplete-suggestion', 'list-group-item', 'list-group-item-action');
            suggestionElement.textContent = suggestion.display_name;

            suggestionElement.addEventListener('click', () => {
                addressInput.value = suggestion.display_name;
                clearSuggestions(suggestionsContainer);
            });

            suggestionsContainer.appendChild(suggestionElement);
        });
    }, 600);

    addressInput.addEventListener('input', handleInput);
    document.addEventListener('click', (e) => {
        if (!suggestionsContainer.contains(e.target) && e.target !== addressInput) {
            clearSuggestions(suggestionsContainer);
        }
    });
});