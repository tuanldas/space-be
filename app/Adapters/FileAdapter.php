<?php

namespace App\Adapters;

use App\Adapters\Interfaces\FileAdapterInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class FileAdapter implements FileAdapterInterface
{
    /**
     * Disk mặc định được sử dụng để lưu trữ
     *
     * @var string
     */
    private string $defaultDisk;
    
    public function __construct()
    {
        $this->defaultDisk = Config::get('filesystems.default_upload_disk', 'public');
    }
    
    /**
     * @inheritDoc
     */
    public function store(UploadedFile $file, string $directory): array
    {
        $path = $file->store($directory, $this->defaultDisk);
        
        return [
            'path' => $path,
            'disk' => $this->defaultDisk
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function delete(string $path, string $disk): bool
    {
        return Storage::disk($disk)->delete($path);
    }
    
    /**
     * @inheritDoc
     */
    public function getUrl(string $path, string $disk): string
    {
        return Storage::disk($disk)->url($path);
    }
} 