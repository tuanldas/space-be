<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Ramsey\Uuid\Uuid;

class Image extends Model
{
    use HasUuids;

    protected $table = 'images';


    public function newUniqueId(): ?string
    {
        return Uuid::uuid7()->toString();
    }

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}
