<?php

namespace HotwiredLaravel\TurboLaravel\Broadcasting;

class Limiter
{
    public function __construct(protected array $keys = [], protected int $delay = 500) {}

    public function clear(): void
    {
        $this->keys = [];
    }

    public function shouldLimit(string $key): bool
    {
        if (! isset($this->keys[$key]) || $this->keys[$key]->isPast()) {
            $this->keys[$key] = now()->addMilliseconds($this->delay);

            return false;
        }

        return true;
    }
}
