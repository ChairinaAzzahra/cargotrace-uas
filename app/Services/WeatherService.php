<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WeatherService
{
    /**
     * Fetch current weather data for given coordinates (UAS Brief slide 9).
     * Uses Laravel HTTP Client with timeout, error handling, and caching.
     *
     * @return array{available: bool, temperature: ?float, wind_speed: ?float, condition: string, code: ?int, message: ?string}
     */
    public function getCurrentWeather(float $latitude, float $longitude): array
    {
        $cacheKey = sprintf('weather_%.3f_%.3f', $latitude, $longitude);

        return Cache::remember($cacheKey, config('cargotrace.weather_cache_ttl', 3600), function () use ($latitude, $longitude) {
            try {
                $endpoint = config('cargotrace.weather_api_url', 'https://api.open-meteo.com/v1/forecast');

                $response = Http::timeout(5)->get($endpoint, [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'current_weather' => 'true',
                ]);

                if (!$response->successful()) {
                    Log::warning('Weather API returned non-success HTTP status', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    return [
                        'available' => false,
                        'temperature' => null,
                        'wind_speed' => null,
                        'condition' => 'Service Unavailable',
                        'code' => null,
                        'message' => 'External weather service temporarily unavailable (HTTP ' . $response->status() . ').',
                    ];
                }

                $data = $response->json();
                $current = $data['current_weather'] ?? null;

                if (!$current) {
                    return [
                        'available' => false,
                        'temperature' => null,
                        'wind_speed' => null,
                        'condition' => 'Data Not Available',
                        'code' => null,
                        'message' => 'No current weather data returned for this coordinate.',
                    ];
                }

                $weatherCode = $current['weathercode'] ?? 0;
                $condition = $this->interpretWeatherCode($weatherCode);

                return [
                    'available' => true,
                    'temperature' => (float) ($current['temperature'] ?? 0),
                    'wind_speed' => (float) ($current['windspeed'] ?? 0),
                    'condition' => $condition,
                    'code' => $weatherCode,
                    'time' => $current['time'] ?? now()->toIso8601String(),
                    'message' => null,
                ];
            } catch (Throwable $e) {
                Log::error('Weather API request failed with exception', [
                    'message' => $e->getMessage(),
                ]);

                return [
                    'available' => false,
                    'temperature' => null,
                    'wind_speed' => null,
                    'condition' => 'Connection Timeout/Error',
                    'code' => null,
                    'message' => 'Unable to contact weather service: ' . $e->getMessage(),
                ];
            }
        });
    }

    /**
     * Map WMO Weather interpretation codes to human-readable text.
     */
    protected function interpretWeatherCode(int $code): string
    {
        return match (true) {
            $code === 0 => 'Clear Sky',
            in_array($code, [1, 2, 3]) => 'Partly Cloudy',
            in_array($code, [45, 48]) => 'Foggy',
            in_array($code, [51, 53, 55]) => 'Drizzle',
            in_array($code, [61, 63, 65]) => 'Rain',
            in_array($code, [71, 73, 75]) => 'Snowfall',
            in_array($code, [80, 81, 82]) => 'Rain Showers',
            in_array($code, [95, 96, 99]) => 'Thunderstorm / Storm Advisory',
            default => 'Clear / Moderate Conditions',
        };
    }
}
