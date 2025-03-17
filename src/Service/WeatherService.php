<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class WeatherService
{
    private string $apiKey;
    private string $apiUrl;
    private string $logFile;
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->apiKey = $_ENV['WEATHER_API_KEY'] ?? getenv('WEATHER_API_KEY');
        $this->apiUrl = $_ENV['WEATHER_API_URL'] ?? getenv('WEATHER_API_URL');
        $this->logFile = dirname(__DIR__, 2) . '/var/log/weather_log.txt';
        $this->httpClient = $httpClient;
    }

    /**
     * Get weather data for a specific city
     *
     * @param string $city The city name
     * @return array Weather data or error information
     */
    public function getWeatherData(string $city): array
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
                return ['error' => $data['error']['message']];
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
            
            $this->logWeatherData($result);
            
            return $result;
            
        } catch (ClientExceptionInterface $e) {
            return ['error' => 'Client error: ' . $e->getMessage()];
        } catch (ServerExceptionInterface $e) {
            return ['error' => 'Server error: ' . $e->getMessage()];
        } catch (TransportExceptionInterface $e) {
            return ['error' => 'Transport error: ' . $e->getMessage()];
        } catch (\Exception $e) {
            return ['error' => 'Unexpected error: ' . $e->getMessage()];
        }
    }

    /**
     * Log weather data to a file
     *
     * @param array $weatherData The weather data to log
     * @return void
     */
    private function logWeatherData(array $weatherData): void
    {
        // Create log directory if it doesn't exist
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logMessage = date('Y-m-d H:i:s') . " - Weather in {$weatherData['city']}: {$weatherData['temperature']}°C, {$weatherData['condition']}\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
}
