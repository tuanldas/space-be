<?php

namespace App\Adapters\ViewModels;

use App\Domain\ViewModels\ViewModel;
use App\Http\Resources\ImageResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResourceViewModel implements ViewModel
{
    public function __construct(
        private readonly ImageResource|JsonResource $resource,
        private int                                 $statusCode = 200,
    )
    {
    }

    public function getResource(): ImageResource|JsonResource
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
