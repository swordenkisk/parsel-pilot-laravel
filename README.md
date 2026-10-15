# Parsel Pilot for Laravel

Parsel Pilot is a small Laravel package for calling parcel API endpoints from
a Laravel application. It provides a typed client, configurable endpoint paths
and headers, and consistent exceptions for unsuccessful or malformed responses.

The reference project is a Laravel package for working with Yalidine's parcel
API. Parsel Pilot is an independent implementation: it does not copy that
project's source code.

## Features

- Retrieve parcel collections and individual parcels.
- Submit parcel creation payloads.
- Retrieve delivery fees and tracking information.
- Configure API routes, authentication header names, and timeout.
- Avoid logging credentials, request bodies, or provider response bodies.
- Test API calls without making real network requests.

## Requirements

- PHP 8.2 or newer.
- Laravel 10, 11, or 12.
- Composer.

## Installation

Install from the local package directory while developing:

```bash
composer config repositories.parsel-pilot path ../parsel-pilot-laravel
composer require swordenkisk/parsel-pilot-laravel:@dev

Or, after publishing the package to a Composer repository:

bash


composer require swordenkisk/parsel-pilot-laravel
Laravel should discover the service provider automatically. To publish the
configuration file:

bash


php artisan vendor:publish --tag=parsel-pilot-config
Configuration
Copy the relevant values from .env.example into your application's .env.
Use the provider's official documentation to set the real base URL, authentication
header names, and endpoint paths. The example endpoint paths and header names in
this repository are placeholders; do not assume they match a live provider API.

Keep credentials in environment variables or a secrets manager. Do not commit
.env files, credentials, or real customer shipment data.

Usage
Resolve the client from Laravel's container:

php


use ParselPilot\YalidineClient;

\$client = app(YalidineClient::class);

\$parcels = \$client->getParcels([
    'status' => 'ready',
]);

\$parcel = \$client->getParcel('TRACKING-123');

\$created = \$client->createParcel([
    // Supply the fields required by the provider's documented API schema.
]);

\$fees = \$client->getDeliveryFees([
    // Supply provider-specific query parameters.
]);

\$tracking = \$client->trackParcel('TRACKING-123');
The methods return decoded JSON as PHP arrays. An unsuccessful HTTP response
throws ParselPilot\ApiException; its message contains the HTTP status but not
the provider's response body. A successful response that is not JSON throws
ParselPilot\InvalidResponseException. Network connection failures may also
raise Laravel's connection exception.

Testing
Install dependencies and run the test suite:

bash


composer install
composer test
Tests use Laravel's HTTP client fake and do not contact a live service.

Deployment and rollback
Confirm the provider's current API contract in a non-production environment.
Configure secrets through the deployment environment, not source control.
Run the test suite and deploy the package version with the application.
For rollback, restore the prior application release and its Composer lockfile. Avoid changing a production lockfile manually during an incident.
Security, data handling, and recovery
The package does not persist parcel data or create audit logs. Applications that
need traceability should log a non-sensitive internal operation ID, outcome,
and timestamp; never log API credentials, full customer addresses, phone numbers,
or raw request/response bodies by default.

For operational recovery, keep application data backups in the host application's
database and backup system. This package itself has no database to back up.

The package does not implement authorization for your application's users.
Protect any controller or job that calls it with your application's normal
authentication and authorization checks.

Acceptance criteria
The client can call each configured operation with the configured base URL, headers, and timeout.
Tracking numbers are URL-encoded and empty values are rejected.
Successful JSON responses return arrays.
Non-success HTTP responses expose their status without exposing response bodies.
Tests run without a live API account or network access.
Limitations
The package does not define the provider's parcel schema, guarantee delivery,
or provide a dashboard, queue, webhook receiver, or automatic retry policy.
Confirm route, authentication, and payload requirements with the API provider
before production use. Automatic retries are intentionally omitted because
retrying parcel creation can create duplicate shipments unless the provider
supports idempotency.

Contributing
Create a branch for your change.
Add or update tests for changed behavior.
Run composer test.
Open a pull request describing the change and any API-contract assumptions.
Please do not include credentials, customer data, or unlicensed provider code in
issues or pull requests.

Intellectual property
This repository contains an independent implementation. Before publishing or
redistributing it, review the provider's terms and the source project's license.
The package declares the MIT license; replace or confirm the copyright and
licensing details for your own repository before release.




**Before using this with a live API:** confirm the provider’s actual endpoints, header names, and payload formats, then update `config/parsel-pilot.php` and the tests to match. Don’t treat the illustrative defaults as production-ready API settings.