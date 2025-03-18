<?php

/**
 * This file is part of the Weather Application.
 */

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Controller for handling weather-related requests.
 */
class WeatherController extends AbstractController
{
    private WeatherService $weatherService;
    private ParameterBagInterface $parameterBag;
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param WeatherService        $weatherService Weather service for fetching weather data
     * @param ParameterBagInterface $parameterBag   Parameter bag for accessing configuration
     */
    public function __construct(WeatherService $weatherService, ParameterBagInterface $parameterBag, TranslatorInterface $translator)
    {
        $this->weatherService = $weatherService;
        $this->parameterBag = $parameterBag;
        $this->translator = $translator;
    }

    #[Route('/', name: 'root_redirect')]
    /**
     * Redirects root URL to the default locale version.
     *
     * @param Request $request The request object
     *
     * @return RedirectResponse A redirect to the localized homepage
     */
    public function rootRedirect(Request $request): RedirectResponse
    {
        $defaultLocale = $this->parameterBag->get('app.locale');

        return $this->redirectToRoute('weather_index', ['_locale' => $defaultLocale]);
    }

    #[Route('/{_locale}', name: 'weather_index', methods: ['GET', 'POST'], requirements: ['_locale' => 'en|uk'])]
    /**
     * Renders the weather application homepage.
     *
     * @param Request $request The request object
     *
     * @return Response The rendered template
     */
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

    #[Route('/api', name: 'weather_api_base', methods: ['GET'])]
    /**
     * Base API endpoint that returns an error when no city is provided.
     *
     * @param Request $request The request object
     *
     * @return Response JSON response with error message
     */
    public function getWeatherApiBase(Request $request): Response
    {
        return $this->json(['error' => 'City parameter is required.'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/api/{city}', name: 'weather_api', methods: ['GET'])]
    /**
     * API endpoint for getting weather data for a specific city.
     *
     * @param Request $request The request object
     * @param string  $city    The city name to get weather for
     *
     * @return Response JSON response with weather data
     */
    public function getWeatherApi(Request $request, string $city): Response
    {
        $weatherData = $this->weatherService->getWeatherData($city);

        return $this->json($weatherData);
    }
}
