<?php

namespace App\Service;

class WeatherService
{
    private string $apiKey;
    private string $apiUrl;
    private string $logFile;

    public function __construct()
    {
        // ToDo: decide on $_ENV or getenv.
        $this->apiKey = $_ENV['WEATHER_API_KEY'] ?? getenv('WEATHER_API_KEY');
        $this->apiUrl = $_ENV['WEATHER_API_URL'] ?? getenv('WEATHER_API_URL');
        $this->logFile = dirname(__DIR__, 2) . '/var/log/weather_log.txt';
    }

    /**
     * Get weather data for a specific city
     *
     * @param string $city The city name
     * @return array Weather data or error information
     */
    public function getWeatherData(string $city): array
    {
        $url = "{$this->apiUrl}?key={$this->apiKey}&q={$city}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $error = 'Curl error: ' . curl_error($ch);
            curl_close($ch);
            return ['error' => $error];
        }
        
        curl_close($ch);
        $data = json_decode($response, true);
        
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
