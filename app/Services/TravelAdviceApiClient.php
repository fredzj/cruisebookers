<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin HTTP client for the private "Nederland Wereldwijd" widget API.
 *
 * The API replaces direct database access for travel advice data. It exposes
 * two read-only GET endpoints, secured with a shared bearer token:
 *
 *   ?action=countries&whitelist=ESP,FRA  → country overview (location + map)
 *   ?action=country&iso=ESP              → country + its travel advices
 */
class TravelAdviceApiClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $token,
        private readonly int $timeout = 10,
    ) {
    }

    /**
     * Build a client from the application configuration.
     */
    public static function fromConfig(): self
    {
        return new self(
            (string) config('services.travlr.base_url', ''),
            (string) config('services.travlr.token', ''),
            (int) config('services.travlr.timeout', 10),
        );
    }

    /**
     * Fetch the country overview for the given ISO 3166-1 alpha-3 whitelist.
     *
     * @param  list<string>  $whitelist  Upper-case alpha-3 ISO codes.
     * @return list<array<string, mixed>>
     */
    public function countries(array $whitelist): array
    {
        if ($whitelist === []) {
            return [];
        }

        $data = $this->get('countries', ['whitelist' => implode(',', $whitelist)]);

        return is_array($data['countries'] ?? null) ? $data['countries'] : [];
    }

    /**
     * Fetch a single country and its travel advices.
     *
     * @return array{country: array<string, mixed>|null, advices: list<array<string, mixed>>}
     */
    public function country(string $iso): array
    {
        $data = $this->get('country', ['iso' => $iso]);

        return [
            'country' => is_array($data['country'] ?? null) ? $data['country'] : null,
            'advices' => is_array($data['advices'] ?? null) ? $data['advices'] : [],
        ];
    }

    /**
     * Perform an authenticated GET request and return the decoded JSON.
     *
     * @param  array<string, string>  $params
     * @return array<string, mixed>
     *
     * @throws RuntimeException On configuration, transport or HTTP errors.
     */
    private function get(string $action, array $params): array
    {
        if ($this->baseUrl === '' || $this->token === '') {
            throw new RuntimeException('De reisadvies-API is niet geconfigureerd.');
        }

        $response = Http::timeout($this->timeout)
            ->acceptJson()
            ->withToken($this->token)
            ->get($this->baseUrl, ['action' => $action] + $params);

        if ($response->failed()) {
            $message = $response->json('error');
            throw new RuntimeException(
                is_string($message) ? $message : 'API-fout (HTTP ' . $response->status() . ').'
            );
        }

        $data = $response->json();

        return is_array($data) ? $data : [];
    }
}
