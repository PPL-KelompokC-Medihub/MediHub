<?php

namespace App\Support\Cache;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

class LaravelCacheItem implements CacheItemInterface
{
    private mixed $value;

    private bool $hit;

    private DateTimeInterface|int|null $expiration = null;

    public function __construct(
        private readonly string $key,
        mixed $value = null,
        bool $hit = false,
    ) {
        $this->value = $value;
        $this->hit = $hit;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->hit ? $this->value : null;
    }

    public function isHit(): bool
    {
        return $this->hit;
    }

    public function set(mixed $value): static
    {
        $this->value = $value;
        $this->hit = true;

        return $this;
    }

    public function expiresAt(?DateTimeInterface $expiration): static
    {
        $this->expiration = $expiration;

        return $this;
    }

    public function expiresAfter(int|DateInterval|null $time): static
    {
        if ($time instanceof DateInterval) {
            $this->expiration = (new DateTimeImmutable())->add($time);
        } else {
            $this->expiration = $time;
        }

        return $this;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function expiration(): DateTimeInterface|int|null
    {
        return $this->expiration;
    }
}
