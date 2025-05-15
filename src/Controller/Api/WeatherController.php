<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/weather', name: 'app_api_weather_')]
class WeatherController extends AbstractController
{
    #[Route('/', name: 'get')]
    public function geatWeather(Request $request): JsonResponse
     {
        // Récupérer la ville depuis les paramètres de requête
        $city = $request->query->get('city');

        if (!$city) {
            return new JsonResponse(['error' => 'Le paramètre "city" est requis.'], 400);
        }

        // Récupérer la clé API depuis l'environnement
        $apiKey = $_ENV['OPENWEATHER_API_KEY'];

        // Construire l'URL de l'API OpenWeatherMap
        $url = sprintf(
            'https://api.openweathermap.org/data/2.5/weather?q=%s&appid=%s&units=metric&lang=fr',
            urlencode($city),
            $apiKey
        );

        try {
            // Appeler l'API OpenWeatherMap
            $weatherResponse = file_get_contents($url);
            $weatherData = json_decode($weatherResponse, true);

            // Retourner les données JSON au front-end
            return new JsonResponse($weatherData);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Impossible de récupérer les données météo.'], 500);
        }
    }
}
