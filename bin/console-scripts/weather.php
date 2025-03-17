<?php

require dirname(__DIR__, 1) . '/../vendor/autoload.php';

use App\Service\WeatherService;

// Get city from command line arguments or use default
$city = $argv[1] ?? 'London';

// Create weather service
$weatherService = new WeatherService();

// Get weather data
$weatherData = $weatherService->getWeatherData($city);

// Display results
if (isset($weatherData['error'])) {
    echo "Error: " . $weatherData['error'] . PHP_EOL;
} else {
    echo "Current weather in {$weatherData['city']}, {$weatherData['country']}:" . PHP_EOL;
    echo "Temperature: {$weatherData['temperature']}°C" . PHP_EOL;
    echo "Condition: {$weatherData['condition']}" . PHP_EOL;
    echo "Humidity: {$weatherData['humidity']}%" . PHP_EOL;
    echo "Wind Speed: {$weatherData['wind_speed']} km/h" . PHP_EOL;
    echo "Last Updated: {$weatherData['last_updated']}" . PHP_EOL;
}
