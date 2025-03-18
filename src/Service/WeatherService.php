<?php

/**
 * This file is part of the Weather Application.
 */

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Service for fetching weather information from external API.
 */
class WeatherService
{
    private string $apiKey;
    private string $apiUrl;
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private CacheInterface $cache;
    private int $cacheTtl;

    /**
     * Constructor.
     *
     * @param HttpClientInterface $httpClient HTTP client for making API requests
     * @param LoggerInterface     $logger     Logger for recording operations
     * @param CacheInterface      $cache      Cache for storing API responses
     */
    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger, CacheInterface $cache)
    {
        $this->apiKey = $_ENV['WEATHER_API_KEY'] ?? getenv('WEATHER_API_KEY');
        $this->apiUrl = $_ENV['WEATHER_API_URL'] ?? getenv('WEATHER_API_URL');
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->cache = $cache;
        // Cache weather data for 30 minutes by default, can be adjusted as needed
        $this->cacheTtl = (int) ($_ENV['WEATHER_CACHE_TTL'] ?? 1800);
    }

    /**
     * Get weather data for a specific city
     *
     * @param string $city         The city name
     * @param bool   $forceRefresh Whether to bypass cache and force a fresh API call
     *
     * @return array Weather data or error information
     */
    public function getWeatherData(string $city, bool $forceRefresh = false): array
    {
        $cacheKey = 'weather_'.strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $city));

        // If force refresh, delete existing cache item
        if ($forceRefresh) {
            $this->cache->delete($cacheKey);
            $this->logger->info('Cache bypassed for weather data', ['city' => $city]);
        }

        try {
            // Return cached data or fetch new data if not in cache
            return $this->cache->get($cacheKey, function (ItemInterface $item) use ($city) {
                $item->expiresAfter($this->cacheTtl);
                $this->logger->info('Weather data not in cache, fetching from API', ['city' => $city]);

                return $this->fetchWeatherFromApi($city);
            });
        } catch (\Exception $e) {
            $this->logger->error('Cache error', [
                'city' => $city,
                'error' => $e->getMessage(),
            ]);

            // Fallback to direct API call if caching fails
            return $this->fetchWeatherFromApi($city);
        }
    }

    /**
     * Fetch weather data directly from the API
     *
     * @param string $city The city name
     *
     * @return array Weather data or error information
     */
    private function fetchWeatherFromApi(string $city): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->apiUrl, [
                'query' => [
                    'key' => $this->apiKey,
                    'q' => $city,
                ],
                'timeout' => 30,
            ]);

            $data = $response->toArray();

            if (isset($data['error'])) {
                $errorMessage = $data['error']['message'];
                $this->logger->error('Weather API error', [
                    'city' => $city,
                    'error' => $errorMessage,
                ]);

                return ['error' => $errorMessage];
            }

            $result = [
                'city' => $data['location']['name'],
                'country' => $data['location']['country'],
                'temperature' => $data['current']['temp_c'],
                'condition' => $data['current']['condition']['text'],
                'humidity' => $data['current']['humidity'],
                'wind_speed' => $data['current']['wind_kph'],
                'last_updated' => $data['current']['last_updated'],
            ];

            $this->logger->info('Weather data retrieved from API', [
                'city' => $result['city'],
                'temperature' => $result['temperature'],
                'condition' => $result['condition'],
            ]);

            return $result;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Weather API client error', [
                'city' => $city,
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'Client error: '.$e->getMessage()];
        } catch (ServerExceptionInterface $e) {
            $this->logger->error('Weather API server error', [
                'city' => $city,
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'Server error: '.$e->getMessage()];
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Weather API transport error', [
                'city' => $city,
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'Transport error: '.$e->getMessage()];
        } catch (\Exception $e) {
            $this->logger->critical('Unexpected error in weather service', [
                'city' => $city,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ['error' => 'Unexpected error: '.$e->getMessage()];
        }
    }
}
