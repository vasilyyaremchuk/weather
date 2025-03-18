<?php

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class WeatherController extends AbstractController
{
    private WeatherService $weatherService;
    private ParameterBagInterface $parameterBag;

    public function __construct(WeatherService $weatherService, ParameterBagInterface $parameterBag)
    {
        $this->weatherService = $weatherService;
        $this->parameterBag = $parameterBag;
    }

    #[Route('/{_locale}/weather', name: 'weather_index', requirements: ['_locale' => 'en|uk'], defaults: ['_locale' => null])]
    public function index(Request $request): Response
    {
        // Set locale from request or .env
        $locale = $request->attributes->get('_locale') ?? $this->parameterBag->get('app.locale');
        $request->setLocale($locale);
        
        return $this->render('weather/index.html.twig', [
            'city' => null,
            'weatherData' => null,
        ]);
    }

    #[Route('/{_locale}/weather/get', name: 'weather_get', methods: ['POST'], requirements: ['_locale' => 'en|uk'], defaults: ['_locale' => null])]
    public function getWeather(Request $request): Response
    {
        // Set locale from request or .env
        $locale = $request->attributes->get('_locale') ?? $this->parameterBag->get('app.locale');
        $request->setLocale($locale);
        
        $city = $request->request->get('city', 'London');
        $weatherData = $this->weatherService->getWeatherData($city);

        return $this->render('weather/index.html.twig', [
            'city' => $city,
            'weatherData' => $weatherData,
        ]);
    }

    #[Route('/{_locale}/weather/api/{city}', name: 'weather_api', methods: ['GET'], requirements: ['_locale' => 'en|uk'], defaults: ['_locale' => null])]
    public function getWeatherApi(Request $request, string $city): Response
    {
        // Set locale from request or .env
        $locale = $request->attributes->get('_locale') ?? $this->parameterBag->get('app.locale');
        $request->setLocale($locale);
        
        $weatherData = $this->weatherService->getWeatherData($city);
        
        return $this->json($weatherData);
    }
}
