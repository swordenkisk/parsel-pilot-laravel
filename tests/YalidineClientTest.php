<?php

namespace ParselPilot\Tests;

use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use InvalidArgumentException;
use ParselPilot\ApiException;
use ParselPilot\YalidineClient;
use PHPUnit\Framework\TestCase;

final class YalidineClientTest extends TestCase
{
    public function test_it_fetches_parcels_with_configured_auth_headers(): void
    {
        $http = new Factory();

        $http->fake(
            static fn (): Response => new Response(
                new PsrResponse(
                    200,
                    ['Content-Type' => 'application/json'],
                    '{"data":[{"id":"P-1"}]}'
                )
            )
        );

        $client = new YalidineClient($http, $this->config());

        self::assertSame(
            ['data' => [['id' => 'P-1']]],
            $client->getParcels(['status' => 'ready'])
        );

        $http->assertSent(static function (Request $request): bool {
            return str_contains($request->url(), '/parcels')
                && $request->hasHeader('X-API-ID', 'demo-id')
                && $request->hasHeader('X-API-TOKEN', 'demo-token');
        });
    }

    public function test_it_encodes_tracking_numbers_in_the_url(): void
    {
        $http = new Factory();

        $http->fake(
            static fn (): Response => new Response(
                new PsrResponse(
                    200,
                    ['Content-Type' => 'application/json'],
                    '{"tracking":"AB-123"}'
                )
            )
        );

        $client = new YalidineClient($http, $this->config());
        $client->getParcel('AB/123');

        $http->assertSent(static fn (Request $request): bool =>
            str_contains($request->url(), 'AB%2F123')
        );
    }

    public function test_it_throws_a_sanitized_exception_for_api_errors(): void
    {
        $http = new Factory();

        $http->fake(
            static fn (): Response => new Response(
                new PsrResponse(
                    401,
                    ['Content-Type' => 'application/json'],
                    '{"error":"sensitive provider details"}'
                )
            )
        );

        $client = new YalidineClient($http, $this->config());

        try {
            $client->getParcels();
            self::fail('Expected an API exception.');
        } catch (ApiException $exception) {
            self::assertSame(401, $exception->statusCode());
            self::assertStringNotContainsString(
                'sensitive provider details',
                $exception->getMessage()
            );
        }
    }

    public function test_it_rejects_an_empty_tracking_number(): void
    {
        $http = new Factory();
        $client = new YalidineClient($http, $this->config());

        $this->expectException(InvalidArgumentException::class);

        $client->getParcel('  ');
    }

    private function config(): array
    {
        return [
            'base_url' => 'https://api.example.test',
            'timeout' => 5,
            'headers' => [
                'X-API-ID' => 'demo-id',
                'X-API-TOKEN' => 'demo-token',
            ],
            'endpoints' => [
                'parcels' => '/parcels',
                'parcel' => '/parcels/{tracking}',
                'create_parcel' => '/parcels',
                'delivery_fees' => '/delivery-fees',
                'tracking' => '/tracking/{tracking}',
            ],
        ];
    }
}
