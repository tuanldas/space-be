<?php

namespace App\Adapters\Presenters\Image;

use App\Adapters\ViewModels\ImageResourceViewModel;
use App\Domain\UseCases\GetImage\GetImageOutput;
use App\Http\Resources\ImageResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GetImageJsonPresenter implements GetImageOutput
{

    public function success($data): ImageResourceViewModel
    {
        return new ImageResourceViewModel(
            new ImageResource($data),
            ResponseAlias::HTTP_OK
        );
    }

    public function notFound(): ImageResourceViewModel
    {
        return new ImageResourceViewModel(
            (new JsonResource([]))
                ->additional([
                    'message' => __('language.not_found', ['attribute' => __('language.image')]),
                ]),
            Response::HTTP_NOT_FOUND
        );
    }

    public function fileNotFound(): ImageResourceViewModel
    {
        return new ImageResourceViewModel(
            (new JsonResource([]))
                ->additional([
                    'message' => __('language.not_found', ['attribute' => __('language.file')]),
                ]),
            Response::HTTP_NOT_FOUND
        );
    }
}
