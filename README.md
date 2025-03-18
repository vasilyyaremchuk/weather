# Weather API Service

## Installation


## Configuration


## Architecture

We use WeatherService to get weather data from the API.

### Some key points

- We use Symfony HttpClient (symfony/http-client) to make requests to the API instead of Curl approach like we have in the original file.
- To implement Logging we use Monolog package.
