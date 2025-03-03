<?php

namespace App\Proxy\Nyt;

use App\Contracts\Nyt\NytApiServiceContract;
use Illuminate\Support\Facades\Cache;

class NytApiServiceProxy implements NytApiServiceContract
{
    private int $cacheTtl;

    public function __construct(private readonly NytApiServiceContract $service)
    {
        $this->cacheTtl = config('services.nyt.bestsellers_cache_ttl');
    }

    public function getBestsellers(array $params): array
    {
        if (!empty($params['isbn']) && is_array($params['isbn'])) {
            return $this->handleMultipleIsbns($params);
        }

        $cacheKey = 'nyt.bestsellers.' . md5(json_encode($params));

        return Cache::remember($cacheKey, $this->cacheTtl, fn() => $this->service->getBestsellers($params));
    }

    private function handleMultipleIsbns(array $params): array
    {
        $results = [];

        foreach ($params['isbn'] as $isbn) {
            $cacheKey = 'nyt.bestsellers.' . md5(
                    json_encode(['isbn' => $isbn] + array_diff_key($params, ['isbn' => 0]))
                );

            $data = Cache::remember($cacheKey, $this->cacheTtl, function () use ($isbn, $params) {
                $params['isbn'] = $isbn;
                return $this->service->getBestsellers($params);
            });

            if (!empty($data['results'])) {
                $results = [...$results, ...$data['results']];
            }
        }

        return [
            'status' => 'OK',
            'num_results' => count($results),
            'results' => $results,
        ];
    }
}
