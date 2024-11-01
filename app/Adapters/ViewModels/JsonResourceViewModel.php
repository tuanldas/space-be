<?php

namespace App\Adapters\ViewModels;

use App\Domain\ViewModels\ViewModel;
use Illuminate\Http\Resources\Json\JsonResource;

class JsonResourceViewModel implements ViewModel
{
    public function __construct(
        private readonly JsonResource $resource,
        private int                   $statusCode = 200,
    )
    {
    }

    public function getResource(): JsonResource
    {
        return $this->resource;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
