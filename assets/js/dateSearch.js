// Can't chosse a date who already pass

// today date
 const dateInput = document.getElementById("search_reservations_form_date");

 // today date on format YYYY-MM-DD
 const today = new Date().toISOString().split('T')[0];

 // Apply to input
 dateInput.setAttribute('min', today);