<?php

namespace App\GraphQL\Queries;

class SystemQuery
{
    public function ping(): string
    {
        return 'pong';
    }
}
