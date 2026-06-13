<?php

namespace App\Support\Cache;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class LaravelCacheItemPool implements CacheItemPoolInterface
{
    /** @var array<string, CacheItemInterface> */
    private array $deferred = [];

    public function __construct(
        private readonly string $prefix,
        private readonly ?Repository $store = null,
    ) {}

    public function getItem(string $key): CacheItemInterface
    {
        $this->validateKey($key);

        $cacheKey = $this->cacheKey($key);
        $store = $this->store();
        $hit = $store->has($cacheKey);

        return new LaravelCacheItem($key, $hit ? $store->get($cacheKey) : null, $hit);
    }

    public function getItems(array $keys = []): iterable
    {
        foreach ($keys as $key) {
            yield $key => $this->getItem($key);
        }
    }

    public function hasItem(string $key): bool
    {
        $this->validateKey($key);

        return $this->store()->has($this->cacheKey($key));
    }

    public function clear(): bool
    {
        return false;
    }

    public function deleteItem(string $key): bool
    {
        $this->validateKey($key);

        return $this->store()->forget($this->cacheKey($key));
    }

    public function deleteItems(array $keys): bool
    {
        $ok = true;

        foreach ($keys as $key) {
            $ok = $this->deleteItem($key) && $ok;
        }

        return $ok;
    }

    public function save(CacheItemInterface $item): bool
    {
        if (! $item instanceof LaravelCacheItem) {
            return false;
        }

        $expiration = $item->expiration();
        if ($expiration === null) {
            return $this->store()->forever($this->cacheKey($item->getKey()), $item->value());
        }

        return $this->store()->put($this->cacheKey($item->getKey()), $item->value(), $expiration);
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferred[$item->getKey()] = $item;

        return true;
    }

    public function commit(): bool
    {
        $ok = true;

        foreach ($this->deferred as $item) {
            $ok = $this->save($item) && $ok;
        }

        $this->deferred = [];

        return $ok;
    }

    private function store(): Repository
    {
        return $this->store ?? Cache::store();
    }

    private function cacheKey(string $key): string
    {
        return $this->prefix.':'.sha1($key);
    }

    private function validateKey(string $key): void
    {
        if ($key === '') {
            throw new \InvalidArgumentException('Invalid PSR-6 cache key.');
        }
    }
}
