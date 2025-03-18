# Weather API Service

## Installation


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

## Set code styandard

```
$ vendor/bin/phpcs --config-set installed_paths vendor/escapestudios/symfony2-coding-standard
```

## Run the code sniffer

```
$ vendor/bin/phpcs --standard=Symfony src
```

## Fix the code sniffer

```
$ vendor/bin/phpcbf --standard=Symfony src
```

