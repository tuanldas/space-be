<?php

namespace App\Models;

use App\Traits\HasImages;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Ramsey\Uuid\Uuid;

class Wallet extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'wallets';

    protected $hidden = [
        'trashed_at',
        'deleted_at',
    ];

    public function newUniqueId(): ?string
    {
        return Uuid::uuid7()->toString();
    }

    public function icon(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
