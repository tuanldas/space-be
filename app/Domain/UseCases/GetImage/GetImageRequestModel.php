<?php

namespace App\Domain\UseCases\GetImage;

readonly class GetImageRequestModel
{
    public function __construct(
        private array $attributes
    )
    {
    }

    public function getUUid()
    {
        return $this->attributes['uuid'];
    }
}
