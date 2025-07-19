<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Symfony\Component\Uid\Uuid;

class TransactionCategory extends Model
{
    use HasUuids, SoftDeletes, HasFactory;
    
    protected $fillable = [
        'name',
        'description',
        'type',
        'user_id',
        'is_default',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_default' => 'boolean',
    ];
    
    protected $with = ['image'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function scopeDefault($query)
    {
        return $query->whereNull('user_id')->where('is_default', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
    
    public function newUniqueId(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
