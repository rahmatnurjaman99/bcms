<?php declare(strict_types=1);

return [
    'routes' => [
        '/graphiql' => [
            'name' => 'graphiql',
            'endpoint' => '/graphql',
            'subscription-endpoint' => env('GRAPHIQL_SUBSCRIPTION_ENDPOINT', null),
            'headers' => array_filter([
                'x-client-id' => env('GRAPHIQL_CLIENT_ID'),
                'x-client-key' => env('GRAPHIQL_CLIENT_KEY'),
            ]),
        ],
    ],

    'enabled' => env('GRAPHIQL_ENABLED', true),
];
