var map = L.map('map').setView([34.7, 135.5], 9); // Zoom sur la région du Kansai

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// Cluster Osaka
var osakaGroup = L.markerClusterGroup();

// Créer groupe pour cities et castles
var citiesGroup = L.layerGroup();  
var castlesGroup = L.layerGroup();  

// Données pour les villes et châteaux, avec des images et descriptions
var markersData = [
  {
    type: 'city',
    title: "Osaka",
    description: "Préfecture d'Osaka.",
    lat: 34.7,
    lon: 135.5,
    image: "/images/map/kansai2.jpg", 
    group: citiesGroup
  },
  {
    type: 'city',
    title: "Kyoto",
    description: "Préfecture de Kyoto.",
    lat: 35.0116,
    lon: 135.7681,
    image: "/images/map/kyoto.jpg",
    group: citiesGroup
  },
  {
    type: 'city',
    title: "Wakayama",
    description: "Capitale de la préfecture de Wakayama.",
    lat: 34.2260,
    lon: 135.1675,
    image: "/images/map/wakayama.jpg",
    group: citiesGroup
  },
  {
    type: 'castle',
    title: "Château de Himeji",
    description: "Patrimoine mondial de l'UNESCO.",
    lat: 34.8394,
    lon: 134.6939,
    image: "/images/map/kansai4.jpg",
    group: castlesGroup
  },
  {
    type: 'castle',
    title: "Château de Hikone",
    description: "Un des châteaux les mieux conservés du Japon.",
    lat: 35.2802,
    lon: 136.2541,
    image: "/images/map/hikone.jpg",
    group: castlesGroup
  },
  {
    type: 'castle',
    title: "Château d'Osaka",
    description: "Un symbole emblématique de la ville d'Osaka.",
    lat: 34.6873,
    lon: 135.5262,
    image: "/images/map/osaka.jpg",
    group: castlesGroup
  }
];

// Créer les markers dynamiquement
markersData.forEach(function(markerData) {
  var marker = L.marker([markerData.lat, markerData.lon]).addTo(markerData.group);
  
  // click event marker
  marker.on('click', function() {
    // Recupere markerData pour le HTML
    document.getElementById('markerModalTitle').innerHTML = `<b>${markerData.title}</b>`; // Titre dynamique
    document.getElementById('markerModalDescription').innerHTML = `${markerData.description}`; // Description dynamique
    document.getElementById('markerModalImage').src = markerData.image; // Image dynamique
    
   
    var myModal = new bootstrap.Modal(document.getElementById('mapModal'));
    myModal.show();
  });
});

// Ajouter les groupes dans le cluster
osakaGroup.addLayer(citiesGroup);
osakaGroup.addLayer(castlesGroup);

// Ajouter le cluster à la carte
map.addLayer(osakaGroup);

// Filtrage
function filterMarkers() {
    var filterCities = document.getElementById("cities").checked;
    var filterCastles = document.getElementById("castles").checked;

    // Vider le cluster
    osakaGroup.clearLayers(); 

    // Filtrage des villes
    if (filterCities) {
        osakaGroup.addLayer(citiesGroup);  // Ajouter les villes au cluster
    }

    // Filtrage des châteaux
    if (filterCastles) {
        osakaGroup.addLayer(castlesGroup); 
    }
    
    map.addLayer(osakaGroup);  
}

document.getElementById("cities").addEventListener("change", filterMarkers);
document.getElementById("castles").addEventListener("change", filterMarkers);

filterMarkers(); // Appliquer le filtrage par défaut