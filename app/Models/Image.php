<?php

namespace App\Models;

use App\Enums\ImageCategoryType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Uid\Uuid;

class Image extends Model
{
    use HasUuids;
    
    protected $fillable = [
        'user_id',
        'disk',
        'path',
        'imageable_type',
        'imageable_id',
        'type',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'type' => ImageCategoryType::class,
    ];

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function newUniqueId(): string
    {
        return Uuid::v7()->toRfc4122();
    }
} 