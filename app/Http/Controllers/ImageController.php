<?php

namespace App\Http\Controllers;

use App\Domain\UseCases\GetImage\GetImageInputPort;
use App\Domain\UseCases\GetImage\GetImageRequestModel;
use App\Http\Resources\ImageResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageController extends Controller
{
    public function __construct(
        private GetImageInputPort $getImageInputPort
    )
    {
    }

    public function show(string $uuid): ImageResource|JsonResource
    {
        $response = $this->getImageInputPort->handle(new GetImageRequestModel(['uuid' => $uuid]));
        return $response->getResource();
    }
}
