<?php

namespace App\Adapters\TokenGenerator;

interface TokenGeneratorInterface
{
    public function generate(string $username, string $password): array;
}
