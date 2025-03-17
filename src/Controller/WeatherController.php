<?php

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeatherController extends AbstractController
{
    private WeatherService $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    #[Route('/weather', name: 'weather_index')]
    public function index(): Response
    {
        return $this->render('weather/index.html.twig', [
            'city' => null,
            'weatherData' => null,
        ]);
    }

    #[Route('/weather/get', name: 'weather_get', methods: ['POST'])]
    public function getWeather(Request $request): Response
    {
        $city = $request->request->get('city', 'London');
        $weatherData = $this->weatherService->getWeatherData($city);

        return $this->render('weather/index.html.twig', [
            'city' => $city,
            'weatherData' => $weatherData,
        ]);
    }

    #[Route('/weather/api/{city}', name: 'weather_api', methods: ['GET'])]
    public function getWeatherApi(string $city): Response
    {
        $weatherData = $this->weatherService->getWeatherData($city);
        
        return $this->json($weatherData);
    }
}
