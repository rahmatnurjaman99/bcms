<?php

namespace App\GraphQL\Mutations;

class SystemMutation
{
    public function noop(): bool
    {
        return true;
    }
}
