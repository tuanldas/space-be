<?php

namespace App\Domain\UseCases\Wallets;

readonly class GetWalletRequestModel
{
    public function __construct(
        private array $attributes
    )
    {
    }

    public function getUserId()
    {
        return $this->attributes['userId'];
    }
}
