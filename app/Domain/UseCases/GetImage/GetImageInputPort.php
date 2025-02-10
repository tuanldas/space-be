<?php

namespace App\Domain\UseCases\GetImage;


use App\Adapters\ViewModels\ImageResourceViewModel;

interface GetImageInputPort
{
    public function handle(GetImageRequestModel $request): ImageResourceViewModel;
}
