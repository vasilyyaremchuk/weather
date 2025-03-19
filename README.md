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


## Architecture

### WeatherService

We use WeatherService to get weather data from the API.
See [src/Service/WeatherService.php](src/Service/WeatherService.php)

In the class WeatherService we use dependency injections to
- HttpClientInterface - to make requests to the API
- LoggerInterface - to log operations
- CacheInterface - to cache API responses
- TranslatorInterface - to translate messages

There we have 2 methods:

- getWeatherData - public method to get weather data either from cache or API
- fetchWeatherFromApi - private helper method to fetch weather data from the API

In the method getWeatherData we use forceRefresh parameter to bypass cache.
We need it in Unit tests to test the business logic but not the cache.

There is also TranslatorInterface - to translate messages. We use it to translate error messages. Only the first error message is translated as the example. It's a question does it make sense to translate error messages? I'm not 100% sure. In the real world project we would need to coordinate it with the stakeholders. But for that task it definitely out of the scope.

### WeatherCommand

We can request the service from the command line:

```
$ php bin/console app:weather:get [city]
```
See [src/Command/WeatherCommand.php](src/Command/WeatherCommand.php)
This command was done for the test purposes and is not used in the application.

### WeatherController

We can request the service from the web:
see [src/Controller/WeatherController.php](src/Controller/WeatherController.php)

We have 3 public methods:

- rootRedirect '/' - redirect root URL to the default locale version
- index '/{_locale}' - main page with weather data
- getWeatherApi '/api/{city}' - API endpoint for getting weather data for a specific city
- getWeatherApiBase '/api' - API endpoint that returns an error when no city is provided

There is a room to improve the code. We can add authorisation to the API endpoint.
But in that case we need to handle with the API key or auth token and probably set up backend to handle with them.

The main page uses [templates/weather/index.html.twig](templates/weather/index.html.twig). This template is extended by [templates/base.html.twig](templates/base.html.twig).

In the base.html.twig we use Bootstrap loaded externally to have some basic styling. But in the production case we should setup style compilation and minification in the application, say, with gulp or webpack.
Also, we can use Tailwind CSS (see https://tailwindcss.com/docs/installation/framework-guides/symfony) or other CSS framework. And as the best practice we can setup Storybook to handle with UI components.

### ErrorController

We need it to show some end user friendly error messages.
see [src/Controller/ErrorController.php](src/Controller/ErrorController.php)

There we use more consistent syntax for dependency injection:

```
public function __construct(private LoggerInterface $logger)
{
}
```

instead of

```
private LoggerInterface $logger;

public function __construct(LoggerInterface $logger) 
{
    $this->logger = $logger;
}
```

The error page uses [templates/error/error.html.twig](templates/error/error.html.twig). This template is extended by [templates/base.html.twig](templates/base.html.twig).

### Some key points

- We use Symfony HttpClient (symfony/http-client) to make requests to the API instead of Curl approach like we have in the original file.
- To implement Logging we use Monolog package.
- To implement Localisation we use Symfony Translator component.
- To implement Caching we use Symfony Cache component.

There is no overengineering here. And as the most complex pattern is the dependency injection. 

We are trying to reuse the basic Symfony components to implement the must have features such as logging, localisation and caching.

### Assets

We use Symfony Assets component to handle with assets. Just for the example the asset('images/logo.svg') was added in the base.html.twig template as a favicon and in the weather/index.html.twig template as a logo. Custom scripts and styles can be added in the same way.

## Logging

We use Monolog package to implement logging.
You can find the logs in the `var/log` directory.
All new weather requests are logged.
If user requests the same city and we get it from cache, it is not logged.

## Localisation

We use Symfony Translator component to implement localisation.
There are 2 locales: en and uk.
You can change the default locale in the .env file.
```
APP_LOCALE=en
```

# Unit tests

## Service

As soon as there is no complex business logic in custom classes, but unit test designed to test internal logic inside classes the only one unit test example looks very basic. We emulate successful result from API and error result from API and test how the service handles them. To be sure that it make simple data transformation correctly. But the actual logic is inside that simple code:

```
$result = [
    'city' => $data['location']['name'],
    'country' => $data['location']['country'],
    'temperature' => $data['current']['temp_c'],
    'condition' => $data['current']['condition']['text'],
    'humidity' => $data['current']['humidity'],
    'wind_speed' => $data['current']['wind_kph'],
    'last_updated' => $data['current']['last_updated'],
];
```
see [tests/Service/WeatherServiceTest.php](tests/Service/WeatherServiceTest.php)

### Unit tests

- testGetWeatherDataSuccess: Tests successful weather data retrieval
- testGetWeatherDataApiError: Tests error handling when the API returns an error

### Run the test in native environment

```
$ php bin/phpunit tests/Service/WeatherServiceTest.php
```

### Run the test in Docker environment

```
$ docker compose exec php vendor/bin/phpunit tests/Service/WeatherServiceTest.php
```

# Code Sniffer

To follow code standards we use Code Sniffer.

## Setup Php Code Sniffer

```
$ composer require --dev squizlabs/php_codesniffer escapestudios/symfony2-coding-standard
$ vendor/bin/phpcs --config-set installed_paths vendor/escapestudios/symfony2-coding-standard
```

## Run the code sniffer

```
$ vendor/bin/phpcs --standard=Symfony src
$ vendor/bin/phpcs --standard=Symfony tests
```

## Run the code sniffer in Docker environment

```
$ docker compose exec php vendor/bin/phpcs --standard=Symfony src
$ docker compose exec php vendor/bin/phpcs --standard=Symfony tests
```

## Automatic fix the code sniffer errors

```
$ vendor/bin/phpcbf --standard=Symfony src
$ vendor/bin/phpcbf --standard=Symfony tests
```

## Automatic fix the code sniffer errors in Docker environment

```
$ docker compose exec php vendor/bin/phpcbf --standard=Symfony src
$ docker compose exec php vendor/bin/phpcbf --standard=Symfony tests
```
