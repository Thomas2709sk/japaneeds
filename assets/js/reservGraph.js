// Définir renderGraph comme une fonction globale
window.renderGraph = function ({ labels, counts, month }) {
    const chartsContainer = document.getElementById('chartsContainer');
  
    // Effacer tout contenu précédent
    while (chartsContainer.firstChild) {
      chartsContainer.removeChild(chartsContainer.firstChild);
    }
  
    // Filtrer les valeurs non nulles (ignorer les 0)
    const filteredCounts = counts.filter(count => count > 0);
  
    // Vérifier si toutes les valeurs non nulles sont identiques
    const allSame = filteredCounts.every(count => count === filteredCounts[0]);
  
    // Si toutes les valeurs non nulles sont identiques ou si aucune valeur non nulle existe
    let barColors;
    if (allSame || filteredCounts.length === 0) {
      barColors = counts.map(() => 'rgba(128, 128, 128, 0.8)');
    } else {
      // Identifier la valeur minimale et maximale pour le mois (en excluant les 0)
      const minReservations = Math.min(...filteredCounts);
      const maxReservations = Math.max(...filteredCounts);
  
      // Générer les couleurs pour chaque barre
      barColors = counts.map(count => {
        if (count === minReservations && count > 0) {
          return 'rgba(255, 99, 132, 0.8)'; 
        } else if (count === maxReservations) {
          return 'rgba(144, 238, 144, 0.8)';
        } else {
          return 'rgba(54, 162, 235, 0.8)';
        }
      });
    }
  
    // Créer un conteneur pour le graphique
    const canvasContainer = document.createElement('div');
    canvasContainer.classList.add('chart-container');
  
    // Créer l'élément <canvas> pour le graphique
    const canvas = document.createElement('canvas');
    canvasContainer.appendChild(canvas); // Ajouter le canvas au conteneur
  
    // Ajouter le conteneur au chartsContainer
    chartsContainer.appendChild(canvasContainer);
  
    // Rendre le graphique avec Chart.js
    const ctx = canvas.getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels, // Jours du mois
        datasets: [{
          label: month, // Mois et année
          data: counts, // Nombre de réservations par jour
          backgroundColor: barColors, // Couleurs dynamiques des barres
          borderColor: barColors.map(color => color.replace('0.8', '1')), // Bordures correspondant aux couleurs
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
              stepSize: 1 // Incréments fixes
            },
            suggestedMax: Math.max(...filteredCounts) + 1 // Ajuste automatiquement le max
          },
          x: {
            ticks: {
              autoSkip: true,
              maxTicksLimit: labels.length // Limite le nombre de ticks
            }
          }
        }
      }
    });
  };