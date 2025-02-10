<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImageResource extends JsonResource
{
    public function toResponse($request): BinaryFileResponse
    {
        $filePath = Storage::disk($this->disk)->path($this->path);

        if (!Storage::disk($this->disk)->exists($this->path)) {
            abort(404, __('language.not_found', ['attribute' => __('language.image')]));
        }

        return response()->file($filePath);
    }
}
