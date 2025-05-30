// Asynchronously fetches weather data for a city
async function fetchWeather(city) {
    try {
        //  Send a request to the backend API to get weather information
        const response = await fetch(`/api/weather?city=${city}`);
        const data = await response.json();

        // If the API responds with an error > return null
        if (data.error) {
            console.error(`Erreur pour ${city}:`, data.error);
            return null;
        }
          // if successful > return the data
        return data;
    } catch (error) {
        console.error(`Erreur lors de la récupération météo pour ${city}:`, error);
        return null;
    }
}

function createWeatherCardDOM(cityName, temperature, description, iconUrl, humidity) {
    // Change city name if needed
    const cityNameMapping = {
        "Préfecture de Tokyo": "Tokyo",
        "Tokyo": "Tokyo",
        "Kyoto": "Kyoto"
    };
    const simplifiedCityName = cityNameMapping[cityName] || cityName;

    //  Create div with all the data 
    const col = document.createElement("div");
    col.className = "col-md-5 col-lg-4  d-flex justify-content-center mb-4";

    const card = document.createElement("div");
    card.className = "card text-center border-0 shadow ";
    card.style.maxWidth = "300px";
    card.style.width = "100%";

    const cardBody = document.createElement("div");
    cardBody.className = "card-body border";

    const title = document.createElement("h3");
    title.className = "card-title mb-3";
    title.textContent = simplifiedCityName;

    const img = document.createElement("img");
    img.src = iconUrl;
    img.alt = description;
    img.style.width = "100px";
    img.className = "mb-3 weather-icon ";

    const temp = document.createElement("h4");
    temp.className = "card-text fw-bold text-primary";
    temp.textContent = `${temperature}°C`;

    const desc = document.createElement("p");
    desc.className = "text-capitalize text-muted";
    desc.textContent = description;

    const hr = document.createElement("hr");

    const humidityP = document.createElement("p");
    humidityP.innerHTML = `<strong>Humidité :</strong> ${humidity}%`;

    // Assemble
    cardBody.append(title, img, temp, desc, hr, humidityP);
    card.appendChild(cardBody);
    col.appendChild(card);

    return col;
}

//  Show weather
async function displayWeather() {
    const cities = ["Tokyo", "Kyoto"];
    const weatherContainer = document.getElementById("meteo");

    for (const city of cities) {
        const weatherData = await fetchWeather(city);

        // If all data > create card
        if (weatherData) {
            const cityName = weatherData.name;
            const temperature = Math.round(weatherData.main.temp);
            const description = weatherData.weather[0].description;
            const iconCode = weatherData.weather[0].icon;
            const iconUrl = `http://openweathermap.org/img/wn/${iconCode}@4x.png`;
            const humidity = weatherData.main.humidity;

            const weatherCard = createWeatherCardDOM(cityName, temperature, description, iconUrl, humidity);


            weatherContainer.appendChild(weatherCard);
        }
    }
}

document.addEventListener("DOMContentLoaded", () => {
    displayWeather();
});
