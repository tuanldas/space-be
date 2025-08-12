<?php

namespace App\Support;

class ServiceResult
{
    private bool $success;
    private mixed $data;
    private ?string $message;
    private int $status;

    private function __construct(bool $success, mixed $data = null, ?string $message = null, int $status = 200)
    {
        $this->success = $success;
        $this->data = $data;
        $this->message = $message;
        $this->status = $status;
    }

    public static function success(mixed $data = null, ?string $message = null, int $status = 200): self
    {
        return new self(true, $data, $message, $status);
    }

    public static function error(string $message, int $status = 500): self
    {
        return new self(false, null, $message, $status);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
} 