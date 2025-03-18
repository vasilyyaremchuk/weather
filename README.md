# Weather API Service

This simple application get the weather data from the API and display it.

## Installation

First of all you need to get your API key from https://www.weatherapi.com/

After that please copy `.env.example` to `.env` and add your API key.

There is 2 options run the application

1. Natively 

```
$ composer install
$ php bin/console server:run
```

2. Using Docker

I use https://github.com/dunglas/symfony-docker approach.

```
$ docker compose build --no-cache
$ docker compose up --pull always -d --wait
```
Open https://localhost in your favorite web browser and accept the auto-generated TLS certificate.

Run `docker compose down --remove-orphans` to stop the Docker containers.

## Configuration


## Architecture

We use WeatherService to get weather data from the API.
see [src/Service/WeatherService.php](src/Service/WeatherService.php)

We can request the service from the command line:

```
$ php bin/console app:weather:get [city]
```
see [src/Command/WeatherCommand.php](src/Command/WeatherCommand.php)
This command was done for the test purposes and is not used in the application.

### Some key points

- We use Symfony HttpClient (symfony/http-client) to make requests to the API instead of Curl approach like we have in the original file.
- To implement Logging we use Monolog package.


# Unit tests

## Service

tests/Service/WeatherServiceTest.php with two test cases:

- testGetWeatherDataSuccess: Tests successful weather data retrieval
- testGetWeatherDataApiError: Tests error handling when the API returns an error

### Run the test

```
$ php bin/phpunit tests/Service/WeatherServiceTest.php
```

# Code Sniffer

To follow code standards we use Code Sniffer.

## Set code standards

```
$ vendor/bin/phpcs --config-set installed_paths vendor/escapestudios/symfony2-coding-standard
```

## Run the code sniffer

```
$ vendor/bin/phpcs --standard=Symfony src
$ vendor/bin/phpcs --standard=Symfony tests
```

## Automatic fix the code sniffer errors

```
$ vendor/bin/phpcbf --standard=Symfony src
$ vendor/bin/phpcbf --standard=Symfony tests
```

