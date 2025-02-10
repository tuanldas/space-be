<?php

namespace App\Domain\UseCases\GetImage;

interface GetImageOutput
{

    public function success($data);

    public function notFound();

    public function fileNotFound();
}
