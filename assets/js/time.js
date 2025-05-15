
let time = document.getElementById('time');
setInterval(() => {
    let d = new Date();
    let options = { timeZone: 'Asia/Tokyo', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    let timeString = d.toLocaleTimeString( 'fr-FR', { timeZone: 'Asia/Tokyo' });
    let dateString = d.toLocaleDateString('fr-FR', options);
    time.innerHTML =`Date et heure de Tokyo: ${dateString} - ${timeString}`;
}, );