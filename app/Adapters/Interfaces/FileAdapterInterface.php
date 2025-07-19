<?php

namespace App\Adapters\Interfaces;

use Illuminate\Http\UploadedFile;

interface FileAdapterInterface
{
    /**
     * Lưu file và trả về đường dẫn
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return array Mảng chứa thông tin về file ['path' => '...', 'disk' => '...']
     */
    public function store(UploadedFile $file, string $directory): array;
    
    /**
     * Xóa file từ storage
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public function delete(string $path, string $disk): bool;
    
    /**
     * Lấy URL đầy đủ của file
     *
     * @param string $path
     * @param string $disk
     * @return string
     */
    public function getUrl(string $path, string $disk): string;
} 