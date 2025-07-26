<?php

namespace App\Models;

use App\Adapters\Interfaces\FileAdapterInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\App;
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
    
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $appends = ['image_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        $fileAdapter = App::make(FileAdapterInterface::class);
        return $fileAdapter->getUrl($this->image->path, $this->image->disk);
    }
    
    /**
     * Chuyển đổi model thành mảng và thay thế thông tin hình ảnh bằng URL
     *
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        // Thay thế thông tin hình ảnh chi tiết bằng URL
        if (isset($array['image'])) {
            $array['image'] = $array['image_url'];
        }
        
        // Xóa trường image_url thừa
        unset($array['image_url']);
        
        return $array;
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

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
