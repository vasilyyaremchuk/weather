<?php

/**
 * This file is part of the Weather Application.
 *
 * You can run this command from the command line:
 *
 * $ php bin/console app:weather:get [city]
 */

namespace App\Command;

use App\Service\WeatherService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to fetch weather information for a specified city.
 */
class WeatherCommand extends Command
{
    protected static $defaultName = 'app:weather:get';
    protected static $defaultDescription = 'Get weather information for a city';

    private WeatherService $weatherService;

    /**
     * Constructor.
     *
     * @param WeatherService $weatherService Service for fetching weather data
     */
    public function __construct(WeatherService $weatherService)
    {
        parent::__construct();
        $this->weatherService = $weatherService;
    }

    /**
     * Configures the command by defining the name, description and arguments.
     */
    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('city', InputArgument::OPTIONAL, 'City name', 'London');
    }

    /**
     * Executes the command to fetch and display weather information.
     *
     * @param InputInterface  $input  Command input
     * @param OutputInterface $output Command output
     *
     * @return int Command status code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $city = $input->getArgument('city');

        $io->title('Weather Information');
        $io->text("Fetching weather data for: $city");

        $weatherData = $this->weatherService->getWeatherData($city);

        if (isset($weatherData['error'])) {
            $io->error($weatherData['error']);

            return Command::FAILURE;
        }

        $io->success("Current weather in {$weatherData['city']}, {$weatherData['country']}");

        $io->table(
            ['Property', 'Value'],
            [
                ['Temperature', $weatherData['temperature'].'°C'],
                ['Condition', $weatherData['condition']],
                ['Humidity', $weatherData['humidity'].'%'],
                ['Wind Speed', $weatherData['wind_speed'].' km/h'],
                ['Last Updated', $weatherData['last_updated']],
            ]
        );

        return Command::SUCCESS;
    }
}
