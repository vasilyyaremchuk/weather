<?php

namespace App\Tests\Service;

use App\Service\WeatherService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class WeatherServiceTest extends TestCase
{
    private $httpClient;
    private $logger;
    private $weatherService;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        
        // Set environment variables for testing
        putenv('WEATHER_API_KEY=test_api_key');
        putenv('WEATHER_API_URL=https://api.weatherapi.com/v1/current.json');
        
        $this->weatherService = new WeatherService($this->httpClient, $this->logger);
    }

    public function testGetWeatherDataSuccess()
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockData = [
            'location' => [
                'name' => 'London',
                'country' => 'UK'
            ],
            'current' => [
                'temp_c' => 20.5,
                'condition' => [
                    'text' => 'Partly cloudy'
                ],
                'humidity' => 65,
                'wind_kph' => 15.5,
                'last_updated' => '2025-03-18 11:00'
            ]
        ];

        $mockResponse->expects($this->once())
            ->method('toArray')
            ->willReturn($mockData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                $this->anything(),
                $this->callback(function ($options) {
                    return isset($options['query']['key']) 
                        && isset($options['query']['q'])
                        && $options['query']['q'] === 'London';
                })
            )
            ->willReturn($mockResponse);

        $result = $this->weatherService->getWeatherData('London');

        $this->assertEquals('London', $result['city']);
        $this->assertEquals('UK', $result['country']);
        $this->assertEquals(20.5, $result['temperature']);
        $this->assertEquals('Partly cloudy', $result['condition']);
        $this->assertEquals(65, $result['humidity']);
        $this->assertEquals(15.5, $result['wind_speed']);
        $this->assertEquals('2025-03-18 11:00', $result['last_updated']);
    }

    public function testGetWeatherDataApiError()
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockData = [
            'error' => [
                'message' => 'Invalid API key'
            ]
        ];

        $mockResponse->expects($this->once())
            ->method('toArray')
            ->willReturn($mockData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Weather API error',
                $this->callback(function ($context) {
                    return $context['error'] === 'Invalid API key';
                })
            );

        $result = $this->weatherService->getWeatherData('London');

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Invalid API key', $result['error']);
    }
}
