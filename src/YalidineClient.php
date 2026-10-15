<?php

namespace ParselPilot;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Response;
use InvalidArgumentException;

final class YalidineClient
{
    public function __construct(
        private readonly Factory $http,
        private readonly array $config
    ) {
    }

    /**
     * Retrieve a parcel collection, optionally filtered by query parameters.
     */
    public function getParcels(array $filters = []): array
    {
        return $this->send(
            'GET',
            $this->endpoint('parcels'),
            $filters
        );
    }

    /**
     * Retrieve one parcel by its tracking number.
     */
    public function getParcel(string $trackingNumber): array
    {
        return $this->send(
            'GET',
            $this->trackingEndpoint('parcel', $trackingNumber)
        );
    }

    /**
     * Submit a parcel payload using the provider's expected schema.
     */
    public function createParcel(array $payload): array
    {
        return $this->send(
            'POST',
            $this->endpoint('create_parcel'),
            [],
            $payload
        );
    }

    /**
     * Retrieve delivery fees using provider-specific query parameters.
     */
    public function getDeliveryFees(array $parameters = []): array
    {
        return $this->send(
            'GET',
            $this->endpoint('delivery_fees'),
            $parameters
        );
    }

    /**
     * Retrieve tracking events for a parcel.
     */
    public function trackParcel(string $trackingNumber): array
    {
        return $this->send(
            'GET',
            $this->trackingEndpoint('tracking', $trackingNumber)
        );
    }

    private function send(
        string $method,
        string $path,
        array $query = [],
        ?array $payload = null
    ): array {
        $baseUrl = rtrim((string) ($this->config['base_url'] ?? ''), '/');

        if ($baseUrl === '') {
            throw new InvalidArgumentException('A parcel API base URL is required.');
        }

        $timeout = (int) ($this->config['timeout'] ?? 10);

        if ($timeout < 1) {
            throw new InvalidArgumentException('The API timeout must be at least one second.');
        }

        $headers = array_filter(
            $this->config['headers'] ?? [],
            static fn ($value): bool => is_string($value) && $value !== ''
        );

        $request = $this->http
            ->withHeaders($headers)
            ->acceptJson()
            ->timeout($timeout);

        $url = $baseUrl . '/' . ltrim($path, '/');

        /** @var Response $response */
        $response = $payload === null
            ? $request->send($method, $url, ['query' => $query])
            : $request->send($method, $url, [
                'query' => $query,
                'json' => $payload,
            ]);

        if (! $response->successful()) {
            // Do not include the response body: it may contain private data.
            throw new ApiException($response->status());
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new InvalidResponseException();
        }

        return $data;
    }

    private function endpoint(string $name): string
    {
        $path = $this->config['endpoints'][$name] ?? null;

        if (! is_string($path) || $path === '') {
            throw new InvalidArgumentException(
                "The '{$name}' API endpoint is not configured."
            );
        }

        return $path;
    }

    private function trackingEndpoint(string $name, string $trackingNumber): string
    {
        if (trim($trackingNumber) === '') {
            throw new InvalidArgumentException('A tracking number is required.');
        }

        return str_replace(
            '{tracking}',
            rawurlencode($trackingNumber),
            $this->endpoint($name)
        );
    }
}
