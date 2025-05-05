document.addEventListener("DOMContentLoaded", function () {
    const fetchData = async () => {
      try {
        const response = await fetch('/admin/graph/reserv');
        if (!response.ok) {
          throw new Error(`Erreur lors de la récupération des données: ${response.statusText}`);
        }
  
        const data = await response.json();
  
        // Remplir le menu de sélection avec les mois disponibles
        const monthSelector = document.getElementById('monthSelector');
        data.forEach((monthData, index) => {
          const option = document.createElement('option');
          option.value = index; // Utiliser l'index comme identifiant unique
          option.textContent = monthData.month; // Nom du mois
          monthSelector.appendChild(option);
        });
  
        // Afficher le graphique du premier mois par défaut
        if (data.length > 0) {
          renderGraph(data[0]); // Appelle la fonction du fichier `reservGraph.js`
        }
  
        // Mettre à jour le graphique lorsque l'utilisateur change de mois
        monthSelector.addEventListener('change', (event) => {
          const selectedIndex = parseInt(event.target.value, 10);
          renderGraph(data[selectedIndex]); // Appelle la fonction du fichier `reservGraph.js`
        });
      } catch (error) {
        console.error('Erreur lors de la récupération des données:', error);
      }
    };
  
    fetchData();
  });