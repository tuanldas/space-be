<?php

namespace App\Repositories;

use App\Domain\Repositories\ImageRepositoryInterface;
use App\Models\Image;

class ImageRepository extends BaseRepository implements ImageRepositoryInterface
{
    public function getModel()
    {
        return Image::class;
    }
}
