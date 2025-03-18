<?php

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class WeatherController extends AbstractController
{
    private WeatherService $weatherService;
    private ParameterBagInterface $parameterBag;

    public function __construct(WeatherService $weatherService, ParameterBagInterface $parameterBag)
    {
        $this->weatherService = $weatherService;
        $this->parameterBag = $parameterBag;
    }

    #[Route('/', name: 'root_redirect')]
    public function rootRedirect(Request $request): Response
    {
        $defaultLocale = $this->parameterBag->get('app.locale');
        return $this->redirectToRoute('weather_index', ['_locale' => $defaultLocale]);
    }

    #[Route('/{_locale}', name: 'weather_index', methods: ['GET', 'POST'], requirements: ['_locale' => 'en|uk'])]
    public function index(Request $request): Response
    {
        // Set locale from request or .env
        $locale = $request->attributes->get('_locale') ?? $this->parameterBag->get('app.locale');
        $request->setLocale($locale);
        
        $city = null;
        $weatherData = null;
        
        if ($request->isMethod('POST')) {
            $city = $request->request->get('city', 'London');
            $weatherData = $this->weatherService->getWeatherData($city);
        }
        
        return $this->render('weather/index.html.twig', [
            'city' => $city,
            'weatherData' => $weatherData,
        ]);
    }

    #[Route('/{_locale}/api/{city}', name: 'weather_api', methods: ['GET'], requirements: ['_locale' => 'en|uk'])]
    public function getWeatherApi(Request $request, string $city): Response
    {
        // Set locale from request or .env
        $locale = $request->attributes->get('_locale') ?? $this->parameterBag->get('app.locale');
        $request->setLocale($locale);
        
        $weatherData = $this->weatherService->getWeatherData($city);
        
        return $this->json($weatherData);
    }
}
