 // Fonction pour définir la date minimale sur la date d'aujourd'hui
 const dateInput = document.getElementById("search_reservations_form_date");

 // Obtenir la date d'aujourd'hui au format YYYY-MM-DD
 const today = new Date().toISOString().split('T')[0];

 // Appliquer la date minimale à l'input
 dateInput.setAttribute('min', today);