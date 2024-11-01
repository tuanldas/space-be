<?php

namespace App\Adapters\Presenters;

use App\Adapters\ViewModels\JsonResourceViewModel;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

trait JsonPresenterHelpers
{
    public function notFound($message): JsonResourceViewModel
    {
        return new JsonResourceViewModel(
            (new JsonResource([]))
                ->additional([
                    'message' => $message,
                ]),
            Response::HTTP_NOT_FOUND
        );
    }
}
