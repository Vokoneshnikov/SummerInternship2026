<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherApiService {
    private HttpClientInterface $client;
    private string $apiKey;
    private string $apiUrl;

    public function __construct(string $apiKey, string $apiUrl, private readonly CacheService $cache) {
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->client = HttpClient::create();
    }
    public function getWeatherData(string $city) : array{
        $cityName = strtolower(trim($city));

        $cachedData = $this->cache->get($cityName);

        if ($cachedData !== null) {
            return $cachedData;
        }

        $data = $this->fetchFromApi($city);

        $this->cache->set($cityName, $data);

        return $data;
    }

    private function fetchFromApi(string $city): array {
        $url = sprintf(
            '%s/weather?q=%s&appid=%s&units=metric',
            $this->apiUrl,
            urlencode($city),
            $this->apiKey
        );
        try {
            $response = $this->client->request('GET', $url);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                $this->handleErrorResponse($statusCode, $response);
            }

            return $response->toArray();
        } catch (TransportExceptionInterface $e) {
            throw new \Exception('Network error: Unable to connect to weather service. Please check your internet connection.');
        } catch (HttpExceptionInterface $e) {
            throw new \Exception('HTTP error: ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception('Unexpected error: ' . $e->getMessage());
        }
    }

    private function handleErrorResponse(int $statusCode, $response): void {
        $content = $response->getContent(false);
        $data = json_decode($content, true);
        $message = $data['message'] ?? 'Unknown error';

        switch ($statusCode) {
            case 400:
                throw new \Exception("Bad request: Please check the city name format.");
            case 401:
                throw new \Exception("Invalid API key: Please check your OpenWeather API key.");
            case 404:
                throw new \Exception("City not found. Please check the spelling.");
            case 429:
                throw new \Exception("Rate limit exceeded: Too many requests. Please wait a moment and try again.");
            case 500:
            case 502:
            case 503:
                throw new \Exception("Server error: OpenWeather service is temporarily unavailable. Please try again later.");
            default:
                throw new \Exception("API error (HTTP {$statusCode}): {$message}");
        }
    }
    /**
     * Очистить кеш для города
     */
    public function clearCache(string $city): void {
        $this->cache->delete(strtolower(trim($city)));
    }
}
