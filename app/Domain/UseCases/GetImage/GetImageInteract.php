<?php

namespace App\Domain\UseCases\GetImage;

use App\Adapters\ViewModels\ImageResourceViewModel;
use App\Domain\Repositories\ImageRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class GetImageInteract implements GetImageInputPort
{
    public function __construct(
        private GetImageOutput           $output,
        private ImageRepositoryInterface $imageRepository
    )
    {
    }

    public function handle(GetImageRequestModel $request): ImageResourceViewModel
    {
        $image = $this->imageRepository->find($request->getUuid());
        if (!$image) {
            return $this->output->notFound();
        }
        if (!Storage::disk($image->disk)->exists($image->path)) {
            return $this->output->fileNotFound();
        }
        return $this->output->success($image);
    }
}
