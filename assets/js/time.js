// Get the <div> with the ID time
let time = document.getElementById('time');
setInterval(() => {
    // Create new Date object
    let d = new Date();
    // Options to choose timezone , and how the time and day will appears
    let options = { timeZone: 'Asia/Tokyo', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
     // Format the current time for the Asia/Tokyo timezone using French formatting
    let timeString = d.toLocaleTimeString( 'fr-FR', { timeZone: 'Asia/Tokyo' });
    // Day with France format
    let dateString = d.toLocaleDateString('fr-FR', options);
    time.textContent =`Date et heure : ${dateString} - ${timeString}`;
}, );