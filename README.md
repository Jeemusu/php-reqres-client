# ReqRes Client

A framework-agnostic PHP library for consuming the [ReqRes API](https://reqres.in/). 

## Features

- Get a single user by ID
- List users with pagination
- Create new users
- PSR-18 HTTP client compatible
- PSR-17 Request factory compatible
- Exception handling

## Installation 

### Install from GitHub

You can install this package directly from GitHub using Composer:

1. **Add the repository to your project's `composer.json`**:
   ```json
   {
       "repositories": [
        {
        "type": "vcs",
        "url": "https://github.com/Jeemusu/php-reqres-client"
        }
    ],
       "require": {
           "jeemusu/php-reqres-client": "dev-main"
       }
   }
   ```

2. **Install the package**:
```bash
composer install
```

### Local Development Installation

If you want to install it locally for development, you can use Composer's path repository feature:

1. **Clone the repository** to your local machine:
   ```bash
   git clone https://github.com/Jeemusu/php-reqres-client.git
   ```

2. **Add the repository to your project's `composer.json`**:
   ```json
   {
       "repositories": [
           {
               "type": "path",
               "url": "../php-reqres-client"
           }
       ],
       "require": {
           "jeemusu/php-reqres-client": "^1.0"
       }
   }
   ```
   > **Note:** Adjust the `url` path to match the relative path from your project to where you cloned the repository.

3. **Install the package**:
   ```bash
   composer install
   ```

## Quick Start

```php
<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Jeemusu\ReqRes\Services\UserService;

// Configure HTTP client
$httpClient = new Client([
    'timeout' => 30,
    'connect_timeout' => 10,
]);

// Create request factory
$httpFactory = new HttpFactory();

// Create user service
$userService = new UserService(
    httpClient: $httpClient,
    requestFactory: $httpFactory,
    baseUri: 'https://reqres.in/api/',
    defaultHeaders: [
        'x-api-key' => 'reqres-free-v1',
        'Accept'    => 'application/json',
    ]
);

// Get a user
$user = $userService->getUserById(1);
echo "User: {$user->firstName} {$user->lastName} ({$user->email})\n";
```

## Usage Examples

### Get Single User

```php
use Jeemusu\ReqRes\Exceptions\UserNotFoundException;
use Jeemusu\ReqRes\Exceptions\ApiException;

try {
    $user = $userService->getUserById(1);
    
    // Access user properties
    echo "ID: {$user->id}\n";
    echo "Name: {$user->firstName} {$user->lastName}\n";
    echo "Email: {$user->email}\n";
    echo "Avatar: {$user->avatar}\n";
    
    // Convert to array
    $userData = $user->toArray();
    print_r($userData);
    
} catch (UserNotFoundException $e) {
    echo "User not found: {$e->getMessage()}\n";
} catch (ApiException $e) {
    echo "API Error: {$e->getMessage()}\n";
    echo "Endpoint: {$e->getEndpoint()}\n";
}
```

### Get Paginated Users

```php
try {
    // Get first page with default 6 users per page
    $userCollection = $userService->getPaginatedUsers();
    
    // Or specify page and per_page
    $userCollection = $userService->getPaginatedUsers(page: 2, perPage: 3);
    
    // Access pagination info
    echo "Page: {$userCollection->page}\n";
    echo "Per Page: {$userCollection->perPage}\n";
    echo "Total Users: {$userCollection->total}\n";
    echo "Total Pages: {$userCollection->totalPages}\n";
    
    // Iterate through users
    foreach ($userCollection->users as $user) {
        echo "- {$user->firstName} {$user->lastName} ({$user->email})\n";
    }
    
    // Convert to array
    $collectionData = $userCollection->toArray();
    
} catch (ApiException $e) {
    echo "API Error: {$e->getMessage()}\n";
}
```

### Create User

```php

try {
    $userId = $userService->createUser(
        name: 'John Doe',
        job: 'Software Engineer'
    );
    
    echo "Created user with ID: {$userId}\n";
    
} catch (ApiException $e) {
    echo "API Error: {$e->getMessage()}\n";
}
```

## HTTP Client Configuration

This library is designed to work with any PSR-18 compatible HTTP client. Here are examples for popular clients:

### Guzzle HTTP

```php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

$httpClient = new Client([
    'timeout' => 30,
    'connect_timeout' => 10,
]);

$httpFactory = new HttpFactory();

$userService = new UserService(
    httpClient: $httpClient,
    requestFactory: $httpFactory,
    baseUri: 'https://reqres.in/api/',
    defaultHeaders: [
        'x-api-key' => 'reqres-free-v1',
        'Accept'    => 'application/json',
    ]
);
```

### Symfony HTTP Client

```php
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\HttpClient;
use Nyholm\Psr7\Factory\Psr17Factory;

$httpClient = new Psr18Client(HttpClient::create([
    'timeout' => 30,
]));

$psr17Factory = new Psr17Factory();

$userService = new UserService(
    httpClient: $httpClient,
    requestFactory: $psr17Factory,
    baseUri: 'https://reqres.in/api/',
    defaultHeaders: [
        'x-api-key' => 'reqres-free-v1',
        'Accept'    => 'application/json',
    ]
);
```

### cURL-based Client

```php
use Http\Client\Curl\Client as CurlClient;
use Nyholm\Psr7\Factory\Psr17Factory;

$httpClient = new CurlClient();
$psr17Factory = new Psr17Factory();

$userService = new UserService(
    httpClient: $httpClient,
    requestFactory: $psr17Factory,
    baseUri: 'https://reqres.in/api/',
    defaultHeaders: [
        'x-api-key' => 'reqres-free-v1',
        'Accept' => 'application/json',
    ]
);
```

## Exception Handling

The library provides specific exceptions for different error scenarios:

```php
use Jeemusu\ReqRes\Exceptions\{
    ApiException,
    UserNotFoundException,
    NetworkException
};

try {
    $user = $userService->getUserById(999);
} catch (UserNotFoundException $e) {
    // Handle 404 - user not found
    echo "User not found: {$e->getMessage()}\n";
} catch (NetworkException $e) {
    // Handle network/connection issues
    echo "Network error: {$e->getMessage()}\n";
} catch (ApiException $e) {
    // Handle other API errors
    echo "API error: {$e->getMessage()}\n";
    echo "Endpoint: {$e->getEndpoint()}\n";
    if ($e->getRequestData()) {
        echo "Request data: " . json_encode($e->getRequestData()) . "\n";
    }
}
```

## Data Transfer Objects

### User

```php
$user = $userService->getUserById(1);

// Properties
$user->id;          // int
$user->email;       // string
$user->firstName;   // string
$user->lastName;    // string
$user->avatar;      // string

// Methods
$user->toArray();   // Convert to array
json_encode($user); // JSON serializable
```

### UserCollection

```php
$collection = $userService->getPaginatedUsers();

// Properties
$collection->page;        // int - Current page
$collection->perPage;     // int - Users per page
$collection->total;       // int - Total users
$collection->totalPages;  // int - Total pages
$collection->users;       // array<User> - Array of User objects

// Methods
$collection->toArray();   // Convert to array
json_encode($collection); // JSON serializable
```

## Development

### Running Tests

```bash
composer test
```

### Static Analysis

```bash
composer stan
```

## Requirements

- PHP 8.4+
- Guzzle HTTP 7.10+
- PSR-18 compatible HTTP client


## Changelog

### v1.0.0

- Initial release
- Support for getting single users
- Support for paginated user lists
- Support for creating users
- DTO models for Users and UserCollections
- Exception handling
- PSR-18: HTTP Client Interface compatibility
- PSR-17: HTTP Factories compatibility

## Todo

- Basic unit test coverate
- Clear phpstan baseline
- Integration tests
- Add Validation for input parameters passed to the createUser method
