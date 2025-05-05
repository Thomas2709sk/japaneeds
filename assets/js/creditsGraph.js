// Fonction pour afficher le graphique des crédits
window.renderCreditsGraph = function ({ labels, counts, month }) {
    if (!labels || !counts || !month) {
        console.error('Données invalides pour renderCreditsGraph :', { labels, counts, month });
        return;
    }

    const chartsContainer = document.getElementById('creditsChartsContainer');

    if (!chartsContainer) {
        console.error('Élément "creditsChartsContainer" introuvable dans le DOM.');
        return;
    }

    // Effacer tout contenu précédent
    while (chartsContainer.firstChild) {
        chartsContainer.removeChild(chartsContainer.firstChild);
    }

    // Filtrer les valeurs non nulles (ignorer les 0)
    const filteredCounts = counts.filter(count => count > 0);

    // Vérifier si toutes les valeurs non nulles sont identiques
    const allSame = filteredCounts.every(count => count === filteredCounts[0]);

    // Générer les couleurs dynamiques pour les barres
    let barColors;
    if (allSame || filteredCounts.length === 0) {
        barColors = counts.map(() => 'rgba(128, 128, 128, 0.8)');
    } else {
        const minCredits = Math.min(...filteredCounts);
        const maxCredits = Math.max(...filteredCounts);

        barColors = counts.map(count => {
            if (count === minCredits && count > 0) {
                return 'rgba(255, 99, 132, 0.8)'; // Couleur pour le minimum
            } else if (count === maxCredits) {
                return 'rgba(144, 238, 144, 0.8)'; // Couleur pour le maximum
            } else {
                return 'rgba(54, 162, 235, 0.8)'; // Couleur par défaut
            }
        });
    }

    // Créer un conteneur pour le graphique
    const canvasContainer = document.createElement('div');
    canvasContainer.classList.add('chart-container');

    const canvas = document.createElement('canvas');
    canvasContainer.appendChild(canvas);

    chartsContainer.appendChild(canvasContainer);

    const ctx = canvas.getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: `Crédits - ${month}`,
                data: counts,
                backgroundColor: barColors,
                borderColor: barColors.map(color => color.replace('0.8', '1')),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    suggestedMax: Math.max(...filteredCounts) + 1
                },
                x: {
                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: labels.length
                    }
                }
            }
        }
    });
};

// Charger les données des crédits
function loadCreditsData() {
    fetch('/admin/credits/graph/credits')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data || data.length === 0) {
                console.error('Aucune donnée valide reçue de l\'API.');
                return;
            }

            // Charger le graphique pour le premier mois par défaut
            if (data[0]) {
                renderCreditsGraph(data[0]);
            }

            const monthSelect = document.getElementById('monthSelector');
            if (!monthSelect) {
                console.error('Élément "monthSelector" introuvable dans le DOM.');
                return;
            }

            monthSelect.innerHTML = '';
            data.forEach((monthData, index) => {
                const option = document.createElement('option');
                option.value = index;
                option.textContent = monthData.month;
                monthSelect.appendChild(option);
            });

            monthSelect.addEventListener('change', (event) => {
                const selectedIndex = event.target.value;
                if (data[selectedIndex]) {
                    renderCreditsGraph(data[selectedIndex]);
                } else {
                    console.error(`Aucune donnée pour l'index sélectionné : ${selectedIndex}`);
                }
            });
        })
        .catch(error => console.error('Erreur lors du chargement des crédits :', error));
}

document.addEventListener('DOMContentLoaded', loadCreditsData);